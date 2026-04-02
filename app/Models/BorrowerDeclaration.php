<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorrowerDeclaration extends Model
{
    protected $table = 'borrower_declarations';

    protected $fillable = [
        'borrower_id',
        'outstanding_judgments',
        'bankruptcy_past_seven_years',
        'foreclosure_past_seven_years',
        'party_to_lawsuit',
        'obligated_on_loan_resulting_foreclosure',
        'delinquent_on_federal_debt',
        'additional_answers',
    ];

    protected function casts(): array
    {
        return [
            'outstanding_judgments' => 'boolean',
            'bankruptcy_past_seven_years' => 'boolean',
            'foreclosure_past_seven_years' => 'boolean',
            'party_to_lawsuit' => 'boolean',
            'obligated_on_loan_resulting_foreclosure' => 'boolean',
            'delinquent_on_federal_debt' => 'boolean',
            'additional_answers' => 'array',
        ];
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }
}
