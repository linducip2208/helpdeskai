<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Events\TicketAssigned;
use App\Events\TicketReplied;
use App\Events\TicketStatusChanged;
use App\Jobs\AnalyzeTicketSentiment;
use App\Jobs\ClassifyTicketWithAi;
use App\Jobs\ScoreReplyQuality;
use App\Jobs\SendChannelMessage;
use App\Mail\TicketCreatedMail;
use App\Mail\TicketReplyMail;
use App\Models\Category;
use App\Models\Department;
use App\Models\Holiday;
use App\Models\Setting;
use App\Models\SlaPolicy;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketLink;
use App\Models\TicketReply;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Watcher;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TicketService
{
    public function __construct(
        protected SlaService $slaService,
        protected AppNotificationService $notifier,
    ) {}

    public function createTicket(array $data): Ticket
    {
        $ticket = DB::transaction(function () use ($data) {
            $data['status'] = $data['status'] ?? TicketStatus::Open->value;
            $data['priority'] = $data['priority'] ?? Setting::get('default_priority', 'medium');
            $data['uid'] = $this->generateUid();
            $data['sla_due_at'] = $this->calculateSlaDueAt($data['department_id'] ?? null, $data['priority'] ?? 'medium');
            $data['sla_response_due_at'] = $this->calculateResponseDueAt($data['department_id'] ?? null, $data['priority'] ?? 'medium');

            $ticket = Ticket::create($data);

            if (! empty($data['body'])) {
                $ticket->replies()->create([
                    'user_id' => $data['user_id'],
                    'body' => $data['body'],
                    'source' => 'web',
                ]);
            }

            ActivityLogService::log(
                'ticket_create',
                $ticket,
                $ticket->subject
            );

            return $ticket;
        });

        ClassifyTicketWithAi::dispatch($ticket->id);

        app(AutomationService::class)->fire('ticket_created', $ticket);

        if (empty($data['assigned_to'])) {
            $assignedId = app(TicketAssignmentService::class)->autoAssign($ticket);
            if ($assignedId) {
                $ticket = $ticket->fresh();
            }
        }

        app(WebhookService::class)->dispatch('ticket.created', $ticket->fresh() ?? $ticket);

        $this->notifyOnCreate($ticket);

        return $ticket->fresh();
    }

    public function assignTicket(Ticket $ticket, int $userId): Ticket
    {
        $previousAssignee = $ticket->assigned_to;
        $ticket->update(['assigned_to' => $userId]);

        ActivityLogService::log(
            'ticket_assign',
            $ticket,
            $ticket->subject,
            ['assigned_to' => $userId]
        );

        if ($userId !== $previousAssignee && $assignee = User::find($userId)) {
            $this->notifier->notify(
                $assignee,
                'ticket.assigned',
                "Ticket assigned: {$ticket->uid}",
                $ticket->subject,
                route('admin.tickets.show', $ticket),
                ['ticket_id' => $ticket->id]
            );
        }

        app(WebhookService::class)->dispatch('ticket.assigned', $ticket->fresh() ?? $ticket, ['assigned_to' => $userId]);
        broadcast(new TicketAssigned($ticket, $userId))->toOthers();

        return $ticket;
    }

    public function changeStatus(Ticket $ticket, string $status): Ticket
    {
        $oldStatus = $ticket->status instanceof TicketStatus ? $ticket->status->value : (string) $ticket->status;

        if ($status === $oldStatus) {
            return $ticket;
        }

        $allowed = self::allowedTransitions()[$oldStatus] ?? [];
        abort_unless(in_array($status, $allowed, true), 422, "Transition from {$oldStatus} to {$status} is not allowed.");

        $this->syncSlaPause($ticket, $oldStatus, $status);
        $ticket->update(['status' => $status]);

        if (in_array($status, ['resolved', 'closed'])) {
            $ticket->update(['closed_at' => now()]);
        }

        if ($status === TicketStatus::Resolved->value) {
            $ticket->update(['resolved_at' => now()]);
        }

        if (in_array($status, [TicketStatus::Open->value, TicketStatus::InProgress->value], true)) {
            $ticket->update(['resolved_at' => null]);
        }

        ActivityLogService::log(
            'ticket_status_change',
            $ticket,
            $ticket->subject,
            ['from' => $oldStatus, 'to' => $status]
        );

        if ($customer = User::find($ticket->user_id)) {
            $this->notifier->notify(
                $customer,
                'ticket.status_changed',
                "Ticket {$ticket->uid} status: {$status}",
                $ticket->subject,
                route('user.tickets.show', $ticket),
                ['ticket_id' => $ticket->id, 'from' => $oldStatus, 'to' => $status]
            );
        }

        app(AutomationService::class)->fire('ticket_status_changed', $ticket, ['from' => $oldStatus, 'to' => $status]);

        app(WebhookService::class)->dispatch('ticket.status_changed', $ticket->fresh() ?? $ticket, ['from' => $oldStatus, 'to' => $status]);
        broadcast(new TicketStatusChanged($ticket, $oldStatus, $status))->toOthers();

        return $ticket;
    }

    /**
     * Central mutation entry point: every channel (web, API, automation,
     * email, widget) must update tickets through here so audit, SLA,
     * automation, webhooks, and notifications stay consistent.
     *
     * @param  array<string, mixed>  $attributes  validated attributes only
     */
    public function updateTicket(Ticket $ticket, array $attributes, ?int $actorId = null): Ticket
    {
        $allowed = ['subject', 'priority', 'department_id', 'category_id', 'assigned_to', 'status', 'custom_fields'];
        $attributes = array_intersect_key($attributes, array_flip($allowed));

        $priorityChanged = array_key_exists('priority', $attributes) && $attributes['priority'] !== $ticket->priority;
        $assignmentChanged = array_key_exists('assigned_to', $attributes) || array_key_exists('department_id', $attributes);

        if (array_key_exists('status', $attributes) && $attributes['status'] !== $ticket->status->value) {
            $this->changeStatus($ticket->fresh(), (string) $attributes['status']);
            unset($attributes['status']);
            $ticket = $ticket->fresh();
        }

        if (array_key_exists('assigned_to', $attributes) && (int) $attributes['assigned_to'] !== (int) $ticket->assigned_to) {
            if ($attributes['assigned_to']) {
                $this->assignTicket($ticket->fresh(), (int) $attributes['assigned_to']);
                $ticket = $ticket->fresh();
            } else {
                $attributes['assigned_to'] = null;
            }
            unset($attributes['assigned_to']);
        }

        if ($attributes === []) {
            return $ticket->fresh();
        }

        $beforeDept = $ticket->department_id;
        $beforePriority = $ticket->priority;

        $ticket->update($attributes);
        $ticket = $ticket->fresh();

        if (($ticket->department_id !== $beforeDept || $ticket->priority !== $beforePriority)
            && $ticket->department_id) {
            $ticket->update([
                'sla_due_at' => $this->calculateSlaDueAt($ticket->department_id, $ticket->priority ?? 'medium'),
                'sla_response_due_at' => $ticket->first_response_at
                    ? $ticket->sla_response_due_at
                    : $this->calculateResponseDueAt($ticket->department_id, $ticket->priority ?? 'medium'),
                'sla_breached' => false,
                'sla_warned_at' => null,
            ]);
            $ticket = $ticket->fresh();
        }

        ActivityLogService::log(
            'ticket_update',
            $ticket,
            $ticket->subject,
            ['changed' => array_keys($attributes), 'by' => $actorId]
        );

        app(AutomationService::class)->fire('ticket_updated', $ticket);
        app(WebhookService::class)->dispatch('ticket.updated', $ticket, ['changed' => array_keys($attributes)]);

        if ($priorityChanged) {
            app(AutomationService::class)->fire('ticket.priority_changed', $ticket);
        }

        if ($assignmentChanged) {
            app(AutomationService::class)->fire('ticket.assignment_changed', $ticket);
        }

        return $ticket;
    }

    public function closeTicket(Ticket $ticket, ?int $actorId = null): Ticket
    {
        abort_if($ticket->status === TicketStatus::Closed, 422, 'Ticket is already closed.');

        $ticket = $this->changeStatus($ticket, TicketStatus::Closed->value);

        ActivityLogService::log('ticket_close', $ticket, $ticket->subject, ['by' => $actorId]);
        app(WebhookService::class)->dispatch('ticket.closed', $ticket->fresh() ?? $ticket, []);

        return $ticket;
    }

    public function reopenTicket(Ticket $ticket, ?int $actorId = null): Ticket
    {
        abort_if($ticket->isOpen(), 422, 'Ticket is already open.');

        $ticket = $this->changeStatus($ticket, TicketStatus::Open->value);

        ActivityLogService::log('ticket_reopen', $ticket, $ticket->subject, ['by' => $actorId]);
        app(AutomationService::class)->fire('ticket_reopened', $ticket->fresh() ?? $ticket);
        app(WebhookService::class)->dispatch('ticket.reopened', $ticket->fresh() ?? $ticket, []);

        return $ticket;
    }

    public function deleteTicket(Ticket $ticket, ?int $actorId = null): void
    {
        DB::transaction(function () use ($ticket, $actorId) {
            ActivityLogService::log('ticket_delete', $ticket, $ticket->subject, [
                'uid' => $ticket->uid,
                'by' => $actorId,
            ]);
            $ticket->delete();
        });
    }

    /**
     * Allowed status transitions. Anything else is rejected with 422.
     * Maps the commercial lifecycle (new/pending/waiting/reopened/cancelled)
     * onto the stored enum values.
     *
     * @return array<string, array<int, string>>
     */
    public static function allowedTransitions(): array
    {
        return [
            'open' => ['in_progress', 'waiting', 'answered', 'resolved', 'closed'],
            'in_progress' => ['waiting', 'answered', 'resolved', 'closed', 'open'],
            'waiting' => ['in_progress', 'answered', 'resolved', 'closed', 'open'],
            'answered' => ['in_progress', 'waiting', 'resolved', 'closed', 'open'],
            'resolved' => ['open', 'closed'],
            'closed' => ['open'],
        ];
    }

    protected function syncSlaPause(Ticket $ticket, string $from, string $to): void
    {
        $policy = $ticket->department_id
            ? SlaPolicy::where('department_id', $ticket->department_id)
                ->where('priority', $ticket->priority ?? 'medium')
                ->where('is_active', true)
                ->first()
            : null;

        if (! $policy || ! $policy->pause_on_waiting) {
            return;
        }

        if ($to === 'waiting' && $ticket->sla_pause_started_at === null) {
            $ticket->update(['sla_pause_started_at' => now()]);
            ActivityLogService::log('sla_pause', $ticket, $ticket->subject, ['status' => $to]);

            return;
        }

        if ($from === 'waiting' && $to !== 'waiting' && $ticket->sla_pause_started_at !== null) {
            $paused = max(0, $ticket->sla_pause_started_at->diffInSeconds(now()));
            $updates = [
                'sla_paused_seconds' => ($ticket->sla_paused_seconds ?? 0) + $paused,
                'sla_pause_started_at' => null,
            ];

            if ($ticket->sla_due_at) {
                $updates['sla_due_at'] = $ticket->sla_due_at->copy()->addSeconds($paused);
            }
            if ($ticket->sla_response_due_at) {
                $updates['sla_response_due_at'] = $ticket->sla_response_due_at->copy()->addSeconds($paused);
            }

            $ticket->update($updates);
            ActivityLogService::log('sla_resume', $ticket, $ticket->subject, ['paused_seconds' => $paused]);
        }
    }

    public function addTag(Ticket $ticket, string $name, ?int $actorId = null): void
    {
        $tag = Tag::firstOrCreate(
            ['name' => mb_substr(trim(mb_strtolower($name)), 0, 100)],
            ['color' => 'blue']
        );

        if (! $ticket->tags()->whereKey($tag->id)->exists()) {
            $ticket->tags()->attach($tag->id);
            ActivityLogService::log('ticket_tag_add', $ticket, $ticket->subject, ['tag' => $tag->name, 'by' => $actorId]);
        }
    }

    public function removeTag(Ticket $ticket, string $name, ?int $actorId = null): void
    {
        $tag = Tag::where('name', mb_strtolower(trim($name)))->first();

        if ($tag) {
            $ticket->tags()->detach($tag->id);
            ActivityLogService::log('ticket_tag_remove', $ticket, $ticket->subject, ['tag' => $tag->name, 'by' => $actorId]);
        }
    }

    public function linkTickets(Ticket $ticket, Ticket $other, string $relation, ?int $actorId = null): void
    {
        abort_if($ticket->id === $other->id, 422, 'Cannot link a ticket to itself.');
        abort_unless(in_array($relation, TicketLink::RELATIONS, true), 422, 'Invalid link relation.');

        TicketLink::firstOrCreate(
            ['ticket_id' => $ticket->id, 'linked_ticket_id' => $other->id, 'relation' => $relation],
            ['user_id' => $actorId]
        );

        ActivityLogService::log('ticket_link', $ticket, $ticket->subject, [
            'linked_uid' => $other->uid, 'relation' => $relation, 'by' => $actorId,
        ]);
    }

    public function unlinkTickets(Ticket $ticket, TicketLink $link, ?int $actorId = null): void
    {
        abort_unless($link->ticket_id === $ticket->id, 404);
        $link->delete();

        ActivityLogService::log('ticket_unlink', $ticket, $ticket->subject, ['link_id' => $link->id, 'by' => $actorId]);
    }

    public function splitTicket(Ticket $ticket, string $subject, string $body, int $actorId): Ticket
    {
        $followUp = $this->createTicket([
            'user_id' => $ticket->user_id,
            'subject' => substr($subject, 0, 255),
            'body' => $body,
            'department_id' => $ticket->department_id,
            'category_id' => $ticket->category_id,
            'priority' => $ticket->priority ?? 'medium',
        ]);

        $this->linkTickets($ticket, $followUp, 'follow_up', $actorId);
        $this->linkTickets($followUp, $ticket, 'parent', $actorId);

        return $followUp;
    }

    public function watch(Ticket $ticket, int $userId): void
    {
        Watcher::firstOrCreate(['ticket_id' => $ticket->id, 'user_id' => $userId]);
    }

    public function unwatch(Ticket $ticket, int $userId): void
    {
        Watcher::where('ticket_id', $ticket->id)->where('user_id', $userId)->delete();
    }

    /**
     * @return array<int, string> usernames mentioned via @name in internal notes
     */
    public function notifyMentions(Ticket $ticket, string $body, int $authorId): array
    {
        preg_match_all('/@([A-Za-z0-9_.-]{2,60})/', $body, $matches);
        $notified = [];

        foreach (array_unique($matches[1]) as $username) {
            $user = User::where('name', $username)->first();

            if (! $user || (int) $user->id === $authorId || ! $user->can('tickets.view')) {
                continue;
            }

            $this->notifier->notify(
                $user,
                'ticket.mentioned',
                "You were mentioned on {$ticket->uid}",
                Str::limit($body, 120),
                route('admin.tickets.show', $ticket),
                ['ticket_id' => $ticket->id]
            );
            $notified[] = $username;
        }

        return $notified;
    }

    /**
     * Execute a macro (reply + status + assign + priority + tags + note).
     *
     * @param  array<string, mixed>  $actions
     */
    public function applyMacro(Ticket $ticket, array $actions, int $actorId): void
    {
        if (! empty($actions['reply'])) {
            $this->addReply($ticket, $actorId, (string) $actions['reply'], false);
            $ticket = $ticket->fresh();
        }

        if (! empty($actions['internal_note'])) {
            $this->addReply($ticket, $actorId, (string) $actions['internal_note'], true);
            $ticket = $ticket->fresh();
        }

        if (! empty($actions['status'])) {
            $this->changeStatus($ticket->fresh(), (string) $actions['status']);
            $ticket = $ticket->fresh();
        }

        if (! empty($actions['priority'])) {
            $this->updateTicket($ticket->fresh(), ['priority' => (string) $actions['priority']], $actorId);
            $ticket = $ticket->fresh();
        }

        if (! empty($actions['assigned_to'])) {
            $this->assignTicket($ticket->fresh(), (int) $actions['assigned_to']);
            $ticket = $ticket->fresh();
        }

        foreach ((array) ($actions['add_tags'] ?? []) as $tag) {
            $this->addTag($ticket, (string) $tag, $actorId);
        }

        foreach ((array) ($actions['remove_tags'] ?? []) as $tag) {
            $this->removeTag($ticket, (string) $tag, $actorId);
        }

        ActivityLogService::log('ticket_macro_applied', $ticket->fresh(), $ticket->subject, ['by' => $actorId]);
    }

    public function addReply(Ticket $ticket, int $userId, string $body, bool $isInternal = false): TicketReply
    {
        $reply = $ticket->replies()->create([
            'user_id' => $userId,
            'body' => $body,
            'is_internal' => $isInternal,
            'source' => 'web',
        ]);

        if ($ticket->status === TicketStatus::Open && ! $isInternal) {
            $ticket->update(['status' => TicketStatus::InProgress->value]);
        }

        if (! $isInternal && $userId !== $ticket->user_id && $ticket->first_response_at === null) {
            $ticket->update(['first_response_at' => now()]);
        }

        ActivityLogService::log(
            'ticket_reply',
            $reply,
            "Reply to ticket #{$ticket->uid}"
        );

        if (! $isInternal && $userId === $ticket->user_id) {
            AnalyzeTicketSentiment::dispatch($reply->id);
        }

        if (! $isInternal && $userId !== $ticket->user_id && Setting::get('ai.qa_enabled', false)) {
            ScoreReplyQuality::dispatch($reply->id);
        }

        if ($isInternal) {
            $this->notifyMentions($ticket, $body, $userId);
        }

        if (! $isInternal) {
            $this->notifyOnReply($ticket, $reply, $userId);
        }

        broadcast(new TicketReplied($ticket, $reply))->toOthers();

        if (! $isInternal && $userId !== $ticket->user_id) {
            app(AutomationService::class)->fire('ticket.agent_replied', $ticket, ['reply_id' => $reply->id]);
        }

        app(AutomationService::class)->fire('ticket_replied', $ticket, ['reply_id' => $reply->id, 'is_internal' => $isInternal]);

        if (! $isInternal) {
            app(WebhookService::class)->dispatch('ticket.replied', $ticket->fresh() ?? $ticket, ['reply_id' => $reply->id]);
        }

        return $reply;
    }

    public static function attachmentRules(): array
    {
        return [
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimetypes:image/jpeg,image/png,image/gif,image/webp,application/pdf,text/plain,text/csv,application/zip,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
    }

    /**
     * Store uploaded files as private ticket attachments.
     *
     * @param  array<int, UploadedFile>  $files
     * @return array<int, TicketAttachment>
     */
    public function addAttachments(Ticket $ticket, ?TicketReply $reply, array $files, int $userId, bool $isInternal = false): array
    {
        $blockedExtensions = ['php', 'phtml', 'exe', 'sh', 'bat', 'cmd', 'com', 'js', 'html', 'htm', 'svg', 'msi', 'dll', 'jar'];

        $stored = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $extension = strtolower($file->getClientOriginalExtension());
            if (in_array($extension, $blockedExtensions, true)) {
                continue;
            }

            $generated = (string) Str::uuid().($extension ? '.'.$extension : '');
            $path = $file->storeAs('attachments/'.$ticket->id, $generated);

            if ($path === false) {
                continue;
            }

            $stored[] = TicketAttachment::create([
                'ticket_id' => $ticket->id,
                'reply_id' => $reply?->id,
                'user_id' => $userId,
                'filename' => $generated,
                'original_name' => substr(basename($file->getClientOriginalName()), 0, 255),
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'size' => $file->getSize() ?? 0,
                'path' => $path,
                'is_internal' => $isInternal,
            ]);
        }

        if ($stored !== []) {
            ActivityLogService::log(
                'ticket_attachment_upload',
                $ticket,
                $ticket->subject,
                ['count' => count($stored), 'reply_id' => $reply?->id]
            );
        }

        return $stored;
    }

    /**
     * Keyword-overlap similarity (deterministic, no AI call).
     *
     * @return array<int, array{id: int, uid: string, subject: string, status: string, score: float}>
     */
    public function similarTickets(Ticket $ticket, int $limit = 5): array
    {
        $terms = array_values(array_unique(array_filter(
            preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($ticket->subject.' '.$ticket->body)),
            fn ($w) => mb_strlen($w) >= 4
        )));

        if ($terms === []) {
            return [];
        }

        $candidates = Ticket::where('id', '!=', $ticket->id)
            ->when($ticket->department_id, fn ($q) => $q->where('department_id', $ticket->department_id))
            ->latest()
            ->take(60)
            ->get(['id', 'uid', 'subject', 'body', 'status', 'department_id']);

        $scored = [];
        foreach ($candidates as $candidate) {
            $haystack = mb_strtolower($candidate->subject.' '.mb_substr($candidate->body ?? '', 0, 2000));
            $hits = 0;
            foreach ($terms as $term) {
                if (str_contains($haystack, $term)) {
                    $hits++;
                }
            }
            if ($hits === 0) {
                continue;
            }
            $score = $hits / max(1, count($terms));
            if ($candidate->department_id && $candidate->department_id === $ticket->department_id) {
                $score += 0.2;
            }
            $scored[] = [
                'id' => $candidate->id,
                'uid' => $candidate->uid,
                'subject' => $candidate->subject,
                'status' => $candidate->status instanceof TicketStatus ? $candidate->status->value : (string) $candidate->status,
                'score' => round(min(1, $score), 2),
            ];
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, max(1, $limit));
    }

    public function logTime(Ticket $ticket, int $userId, int $minutes, ?string $note = null, ?string $workedAt = null): TimeEntry
    {
        $entry = $ticket->timeEntries()->create([
            'user_id' => $userId,
            'minutes' => max(1, $minutes),
            'note' => $note ? substr($note, 0, 255) : null,
            'worked_at' => $workedAt ?? now()->toDateString(),
        ]);

        ActivityLogService::log(
            'ticket_time_logged',
            $ticket,
            $ticket->subject,
            ['minutes' => $entry->minutes, 'entry_id' => $entry->id]
        );

        return $entry;
    }

    public function totalMinutes(Ticket $ticket): int
    {
        return (int) $ticket->timeEntries()->sum('minutes');
    }

    public function mergeTickets(Ticket $primary, Ticket $secondary, int $userId): Ticket
    {
        abort_if($primary->id === $secondary->id, 422, 'Cannot merge a ticket into itself.');
        abort_if($primary->isResolved(), 422, 'Cannot merge into a resolved ticket.');

        return DB::transaction(function () use ($primary, $secondary, $userId) {
            $secondary->replies()->update(['ticket_id' => $primary->id]);
            $secondary->attachments()->update(['ticket_id' => $primary->id]);
            $secondary->timeEntries()->update(['ticket_id' => $primary->id]);

            $primary->replies()->create([
                'user_id' => $userId,
                'body' => "[Merged from {$secondary->uid}] {$secondary->subject}",
                'is_internal' => true,
                'source' => 'web',
            ]);

            $secondary->update(['status' => TicketStatus::Closed->value]);
            $secondary->replies()->create([
                'user_id' => $userId,
                'body' => "[Merged into {$primary->uid}] {$primary->subject}",
                'is_internal' => true,
                'source' => 'web',
            ]);

            ActivityLogService::log(
                'ticket_merge',
                $primary,
                $primary->subject,
                ['merged_ticket_id' => $secondary->id, 'merged_uid' => $secondary->uid]
            );

            return $primary->fresh();
        });
    }

    private function notifyOnCreate(Ticket $ticket): void
    {
        if ($ticket->assigned_to && $agent = User::find($ticket->assigned_to)) {
            $this->notifier->notify(
                $agent,
                'ticket.created',
                "New ticket: {$ticket->uid}",
                $ticket->subject,
                route('admin.tickets.show', $ticket),
                ['ticket_id' => $ticket->id]
            );
        }

        if (Setting::get('notify_new_ticket', true) && $customer = User::find($ticket->user_id)) {
            $ticket->loadMissing('user');
            Mail::to($customer->email)->queue(new TicketCreatedMail($ticket));
        }
    }

    private function notifyOnReply(Ticket $ticket, TicketReply $reply, int $authorId): void
    {
        $isCustomerReply = $authorId === $ticket->user_id;

        if ($isCustomerReply && $ticket->assigned_to) {
            if ($agent = User::find($ticket->assigned_to)) {
                $this->notifier->notify(
                    $agent,
                    'ticket.reply',
                    "Customer reply on {$ticket->uid}",
                    Str::limit($reply->body, 100),
                    route('admin.tickets.show', $ticket),
                    ['ticket_id' => $ticket->id, 'reply_id' => $reply->id]
                );
            }
        }

        if (! $isCustomerReply && $customer = User::find($ticket->user_id)) {
            $this->notifier->notify(
                $customer,
                'ticket.reply',
                "New reply on {$ticket->uid}",
                Str::limit($reply->body, 100),
                route('user.tickets.show', $ticket),
                ['ticket_id' => $ticket->id, 'reply_id' => $reply->id]
            );

            if (Setting::get('notify_ticket_reply', true)) {
                $reply->loadMissing('user');
                Mail::to($customer->email)->queue(new TicketReplyMail($ticket, $reply));
            }

            if ($customer->whatsapp_id || $customer->telegram_id) {
                SendChannelMessage::dispatch($ticket->id, $reply->id);
            }
        }
    }

    public function autoClassify(Ticket $ticket): ?array
    {
        try {
            $aiService = app(AiService::class);

            $departments = Department::where('is_active', true)->pluck('name')->all();
            $categories = Category::where('is_active', true)->pluck('name')->all();

            $body = $ticket->body ?: ($ticket->replies()->oldest()->first()?->body ?? '');

            $messages = [
                [
                    'role' => 'system',
                    'content' => "You are a helpdesk ticket triage assistant. Output ONLY a compact JSON object, no prose, no markdown.\n"
                        .'Schema: {"department":"<exact name from list or null>","category":"<exact name from list or null>","priority":"low|medium|high|urgent","summary":"<one-sentence summary>","confidence":<0.0-1.0 or null>,"reason":"<short reason or null>","language":"<ISO 639-1 code or null>"}'."\n"
                        .'Departments: '.json_encode($departments)."\n"
                        .'Categories: '.json_encode($categories),
                ],
                [
                    'role' => 'user',
                    'content' => "Subject: {$ticket->subject}\n\nBody:\n{$body}",
                ],
            ];

            $result = $aiService->dispatch('ticket.classify', $messages, ['temperature' => 0.2]);

            if (empty($result['content'])) {
                return null;
            }

            $classification = $this->extractJson($result['content']);

            if (! $this->validClassification($classification)) {
                $strict = $messages;
                $strict[0]['content'] .= "\nSTRICT: Return ONLY valid JSON matching the schema. No other text.";
                $retry = $aiService->dispatch('ticket.classify', $strict, ['temperature' => 0]);

                $classification = ! empty($retry['content']) ? $this->extractJson($retry['content']) : null;

                if (! $this->validClassification($classification)) {
                    Log::warning('AI classification output failed schema validation.', [
                        'ticket_id' => $ticket->id,
                    ]);

                    return null;
                }
            }

            $updates = [
                'ai_classification' => $classification + [
                    'provider' => $result['provider'] ?? null,
                    'model' => $result['model'] ?? null,
                    'latency_ms' => $result['latency_ms'] ?? null,
                ],
                'ai_classified_at' => now(),
            ];

            if (! empty($classification['language']) && is_string($classification['language'])) {
                $updates['language'] = mb_substr(mb_strtolower($classification['language']), 0, 8);
            }

            $priority = strtolower($classification['priority'] ?? '');
            if (in_array($priority, ['low', 'medium', 'high', 'urgent'], true)
                && Setting::get('ai.priority_mode', 'automatic') === 'automatic') {
                $updates['priority'] = $priority;
            }

            if (! empty($classification['department']) && ! $ticket->department_id
                && Setting::get('ai.classification_mode', 'automatic') === 'automatic') {
                $dept = Department::where('is_active', true)->whereRaw('LOWER(name) = ?', [strtolower($classification['department'])])->first();
                if ($dept) {
                    $updates['department_id'] = $dept->id;
                }
            }

            if (! empty($classification['category']) && ! $ticket->category_id
                && Setting::get('ai.classification_mode', 'automatic') === 'automatic') {
                $cat = Category::where('is_active', true)->whereRaw('LOWER(name) = ?', [strtolower($classification['category'])])->first();
                if ($cat) {
                    $updates['category_id'] = $cat->id;
                }
            }

            $ticket->update($updates);

            return $classification;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    public function analyzeSentiment(TicketReply $reply): ?string
    {
        try {
            $aiService = app(AiService::class);

            $messages = [
                [
                    'role' => 'system',
                    'content' => 'Classify customer message sentiment. Return ONLY one word: positive, neutral, negative, or angry.',
                ],
                ['role' => 'user', 'content' => $reply->body],
            ];

            $result = $aiService->dispatch('ticket.sentiment', $messages, ['temperature' => 0]);

            if (empty($result['content'])) {
                return null;
            }

            $sentiment = strtolower(trim($result['content']));
            $sentiment = preg_replace('/[^a-z]/', '', $sentiment);

            if (! in_array($sentiment, ['positive', 'neutral', 'negative', 'angry'], true)) {
                return null;
            }

            $reply->update(['sentiment' => $sentiment]);
            $reply->ticket->update(['ai_sentiment' => $sentiment]);

            return $sentiment;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    public function suggestReply(Ticket $ticket): ?string
    {
        try {
            $aiService = app(AiService::class);

            $ticket->loadMissing(['replies.user']);
            $history = $ticket->replies->map(function (TicketReply $r) {
                $role = $r->user?->hasRole('customer') ? 'Customer' : 'Agent';

                return "{$role}: {$r->body}";
            })->implode("\n\n");

            $messages = [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful customer support agent. Draft a professional, empathetic reply. Be concise. Do not invent facts. If the ticket is unclear, ask one clarifying question.',
                ],
                [
                    'role' => 'user',
                    'content' => "Ticket subject: {$ticket->subject}\n\nConversation:\n{$history}\n\nDraft the next agent reply.",
                ],
            ];

            $result = $aiService->dispatch('ticket.suggest', $messages, ['temperature' => 0.5]);

            return $result['content'] ?? null;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    protected function validClassification(mixed $classification): bool
    {
        if (! is_array($classification)) {
            return false;
        }

        if (! isset($classification['priority'])
            || ! in_array(strtolower((string) $classification['priority']), ['low', 'medium', 'high', 'urgent'], true)) {
            return false;
        }

        if (array_key_exists('confidence', $classification) && $classification['confidence'] !== null) {
            if (! is_numeric($classification['confidence'])) {
                return false;
            }
            $confidence = (float) $classification['confidence'];
            if ($confidence < 0 || $confidence > 1) {
                return false;
            }
        }

        return true;
    }

    protected function extractJson(string $content): mixed
    {
        $content = trim($content);

        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/i', $content, $m)) {
            $content = trim($m[1]);
        }

        if (preg_match('/\{[\s\S]*\}/', $content, $m)) {
            $content = $m[0];
        }

        return json_decode($content, true);
    }

    public function calculateSlaDueAt(?int $departmentId = null, string $priority = 'medium'): ?Carbon
    {
        $policy = $this->activePolicy($departmentId, $priority);

        if (! $policy || ! $policy->resolution_time) {
            return null;
        }

        return $this->addPolicyMinutes(now(), $policy, $policy->resolution_time);
    }

    public function calculateResponseDueAt(?int $departmentId = null, string $priority = 'medium'): ?Carbon
    {
        $policy = $this->activePolicy($departmentId, $priority);

        if (! $policy || ! $policy->first_response_time) {
            return null;
        }

        return $this->addPolicyMinutes(now(), $policy, $policy->first_response_time);
    }

    protected function activePolicy(?int $departmentId, string $priority): ?SlaPolicy
    {
        if (! $departmentId) {
            return null;
        }

        return SlaPolicy::where('department_id', $departmentId)
            ->where('priority', $priority)
            ->where('is_active', true)
            ->first();
    }

    protected function addPolicyMinutes(Carbon $start, SlaPolicy $policy, int $minutes): Carbon
    {
        if (! $policy->use_business_hours) {
            return $start->copy()->addMinutes($minutes);
        }

        $holidays = Holiday::query()
            ->pluck('date')
            ->map(fn ($d) => $d instanceof \DateTimeInterface ? $d->format('Y-m-d') : substr((string) $d, 0, 10))
            ->all();

        $due = BusinessHours::addMinutes(
            $start,
            $minutes,
            $policy->workdayList(),
            substr((string) $policy->work_start, 0, 5),
            substr((string) $policy->work_end, 0, 5),
            $policy->timezone ?: 'Asia/Jakarta',
            $holidays
        );

        return $due->setTimezone(config('app.timezone'));
    }

    public function generateUid(): string
    {
        return Ticket::generateUid();
    }
}
