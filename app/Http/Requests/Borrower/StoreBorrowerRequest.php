<?php

namespace App\Http\Requests\Borrower;

use App\Models\Borrower;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBorrowerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Borrower::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'display_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'status' => ['required', 'string', Rule::in(config('borrower.statuses'))],
        ];
    }
}
