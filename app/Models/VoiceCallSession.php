<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoiceCallSession extends Model
{
    protected $fillable = [
        'call_sid',
        'borrower_uuid',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class, 'borrower_uuid', 'uuid');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
