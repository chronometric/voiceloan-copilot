<?php

namespace App\Http\Requests\Borrower;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmploymentRequest extends FormRequest
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
            'employer_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'job_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'years_in_line_of_work' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'monthly_income_cents' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'is_current' => ['sometimes', 'boolean'],
        ];
    }
}
