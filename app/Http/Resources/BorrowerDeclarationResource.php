<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\BorrowerDeclaration
 */
class BorrowerDeclarationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'outstanding_judgments' => $this->outstanding_judgments,
            'bankruptcy_past_seven_years' => $this->bankruptcy_past_seven_years,
            'foreclosure_past_seven_years' => $this->foreclosure_past_seven_years,
            'party_to_lawsuit' => $this->party_to_lawsuit,
            'obligated_on_loan_resulting_foreclosure' => $this->obligated_on_loan_resulting_foreclosure,
            'delinquent_on_federal_debt' => $this->delinquent_on_federal_debt,
            'additional_answers' => $this->additional_answers,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
