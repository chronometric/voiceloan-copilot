<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\BorrowerIdentity
 */
class BorrowerIdentityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'ssn_last4' => $this->ssn_last4,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'citizenship_status' => $this->citizenship_status,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
