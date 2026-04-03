<?php

namespace App\Http\Requests\Borrower;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('borrower'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'middle_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'date_of_birth' => ['sometimes', 'nullable', 'date'],
            'ssn_last4' => ['sometimes', 'nullable', 'digits:4'],
            'address_line1' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address_line2' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'state' => ['sometimes', 'nullable', 'string', 'max:64'],
            'postal_code' => ['sometimes', 'nullable', 'string', 'max:16'],
            'country' => ['sometimes', 'nullable', 'string', 'max:2'],
            'citizenship_status' => ['sometimes', 'nullable', 'string', 'max:64'],
        ];
    }
}
