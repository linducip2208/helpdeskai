<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Category;
use App\Models\Department;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketReply;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        $this->autoClassify($ticket);

        app(AutomationService::class)->fire('ticket_created', $ticket);

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

        return $ticket;
    }

    public function changeStatus(Ticket $ticket, string $status): Ticket
    {
        $oldStatus = $ticket->status->value;
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

        return $ticket;
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
            $this->analyzeSentiment($reply);
        }

        if (! $isInternal) {
            $this->notifyOnReply($ticket, $reply, $userId);
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
                        .'Schema: {"department":"<exact name from list or null>","category":"<exact name from list or null>","priority":"low|medium|high|urgent","summary":"<one-sentence summary>"}'."\n"
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
            if (! is_array($classification)) {
                return null;
            }

            $updates = ['ai_classification' => $classification, 'ai_classified_at' => now()];

            $priority = strtolower($classification['priority'] ?? '');
            if (in_array($priority, ['low', 'medium', 'high', 'urgent'], true)) {
                $updates['priority'] = $priority;
            }

            if (! empty($classification['department']) && ! $ticket->department_id) {
                $dept = Department::where('is_active', true)->whereRaw('LOWER(name) = ?', [strtolower($classification['department'])])->first();
                if ($dept) {
                    $updates['department_id'] = $dept->id;
                }
            }

            if (! empty($classification['category']) && ! $ticket->category_id) {
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

        $holidays = \App\Models\Holiday::query()
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
