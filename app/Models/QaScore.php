<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $reply_id
 * @property int $ticket_id
 * @property float|null $overall
 */
class QaScore extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'accuracy' => 'integer',
            'completeness' => 'integer',
            'tone' => 'integer',
            'empathy' => 'integer',
            'policy_compliance' => 'integer',
            'knowledge_correctness' => 'integer',
            'overall' => 'float',
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
        ];
    }

    public function reply(): BelongsTo
    {
        return $this->belongsTo(TicketReply::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
