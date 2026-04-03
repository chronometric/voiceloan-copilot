<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\BorrowerEmployment
 */
class BorrowerEmploymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employer_name' => $this->employer_name,
            'job_title' => $this->job_title,
            'years_in_line_of_work' => $this->years_in_line_of_work,
            'monthly_income_cents' => $this->monthly_income_cents,
            'is_current' => $this->is_current,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
