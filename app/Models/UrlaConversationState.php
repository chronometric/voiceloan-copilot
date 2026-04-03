<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UrlaConversationState extends Model
{
    protected $fillable = [
        'borrower_id',
        'call_sid',
        'current_stage',
        'current_section',
        'clarification_counts',
        'last_tool_results',
    ];

    protected function casts(): array
    {
        return [
            'clarification_counts' => 'array',
            'last_tool_results' => 'array',
        ];
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }
}
