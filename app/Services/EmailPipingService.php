<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Str;
use Throwable;

class EmailPipingService
{
    public function __construct(
        protected TicketService $ticketService,
    ) {}

    public function processEmail(array $rawEmail): ?Ticket
    {
        $log = EmailLog::create([
            'from_email' => $rawEmail['from'] ?? 'unknown',
            'from_name' => $rawEmail['from_name'] ?? null,
            'to_email' => $rawEmail['to'] ?? (string) config('mail.from.address'),
            'subject' => $rawEmail['subject'] ?? 'No Subject',
            'body_plain' => $rawEmail['text'] ?? $rawEmail['body'] ?? null,
            'body_html' => $rawEmail['html'] ?? null,
            'message_id' => $rawEmail['message_id'] ?? null,
            'in_reply_to' => $rawEmail['in_reply_to'] ?? null,
            'headers' => $rawEmail['headers'] ?? null,
            'direction' => 'inbound',
            'status' => 'received',
            'attachments_count' => count($rawEmail['attachments'] ?? []),
        ]);

        try {
            $ticket = $this->parseAndDispatch($rawEmail, $log);

            $log->update([
                'status' => 'parsed',
                'ticket_id' => $ticket?->id,
                'reply_id' => $log->reply_id,
            ]);

            return $ticket;
        } catch (Throwable $e) {
            $log->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function parseAndDispatch(array $rawEmail, EmailLog $log): ?Ticket
    {
        $fromEmail = $rawEmail['from'] ?? null;
        $subject = $rawEmail['subject'] ?? 'No Subject';
        $body = $rawEmail['body'] ?? $rawEmail['text'] ?? '';

        if (! $fromEmail) {
            throw new \InvalidArgumentException('Missing "from" email address');
        }

        $user = User::where('email', $fromEmail)->first()
            ?? User::create([
                'name' => $rawEmail['from_name'] ?? explode('@', $fromEmail)[0],
                'email' => $fromEmail,
                'password' => bcrypt(Str::random(32)),
            ]);

        if (preg_match('/\[(TKT-[A-Z0-9]+)\]/', $subject, $matches)) {
            $ticket = Ticket::where('uid', $matches[1])->first();

            if ($ticket) {
                $reply = $this->ticketService->addReply($ticket, $user->id, $body, false);
                $log->reply_id = $reply->id;

                return $ticket;
            }
        }

        return $this->ticketService->createTicket([
            'user_id' => $user->id,
            'subject' => $subject,
            'body' => $body,
            'department_id' => null,
            'priority' => 'medium',
        ]);
    }
}
