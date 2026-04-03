<?php

namespace App\Http\Requests\Borrower;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBorrowerRequest extends FormRequest
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
            'display_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
            'status' => ['sometimes', 'string', 'max:32'],
        ];
    }
}
