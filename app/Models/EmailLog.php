<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $from_email
 * @property string|null $from_name
 * @property string $to_email
 * @property string $subject
 * @property string|null $body_plain
 * @property string|null $body_html
 * @property string|null $message_id
 * @property string|null $in_reply_to
 * @property int|null $ticket_id
 * @property int|null $reply_id
 * @property string $direction
 * @property string $status
 * @property string|null $error
 * @property array|null $headers
 * @property int $attachments_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Ticket|null $ticket
 * @property-read TicketReply|null $reply
 */
class EmailLog extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'headers' => 'array',
            'attachments_count' => 'integer',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function reply(): BelongsTo
    {
        return $this->belongsTo(TicketReply::class, 'reply_id');
    }
}
