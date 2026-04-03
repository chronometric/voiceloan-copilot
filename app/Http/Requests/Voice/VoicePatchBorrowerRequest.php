<?php

namespace App\Http\Requests\Voice;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Same field rules as Borrower\UpdateBorrowerRequest; auth is X-Voice-Bridge-Key + active voice session.
 */
class VoicePatchBorrowerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'display_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
            'status' => ['sometimes', 'string', 'max:32'],
        ];
    }
}
