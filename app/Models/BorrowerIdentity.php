<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorrowerIdentity extends Model
{
    protected $table = 'borrower_identity';

    protected $fillable = [
        'borrower_id',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'ssn_last4',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'citizenship_status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'ssn_last4' => 'encrypted',
        ];
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }
}
