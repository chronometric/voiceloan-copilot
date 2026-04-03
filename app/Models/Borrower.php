<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Borrower extends Model
{
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected $fillable = [
        'uuid',
        'created_by_user_id',
        'status',
        'display_name',
        'email',
        'phone',
    ];

    protected static function booted(): void
    {
        static::creating(function (Borrower $borrower): void {
            if (empty($borrower->uuid)) {
                $borrower->uuid = (string) Str::uuid();
            }
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function identity(): HasOne
    {
        return $this->hasOne(BorrowerIdentity::class);
    }

    public function employments(): HasMany
    {
        return $this->hasMany(BorrowerEmployment::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(BorrowerAsset::class);
    }

    public function declaration(): HasOne
    {
        return $this->hasOne(BorrowerDeclaration::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function urlaConversationState(): HasOne
    {
        return $this->hasOne(UrlaConversationState::class);
    }
}
