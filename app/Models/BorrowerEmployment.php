<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorrowerEmployment extends Model
{
    protected $table = 'borrower_employments';

    protected $fillable = [
        'borrower_id',
        'employer_name',
        'job_title',
        'years_in_line_of_work',
        'monthly_income_cents',
        'is_current',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
        ];
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }
}
