<?php

namespace App\Http\Requests\Borrower;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmploymentRequest extends FormRequest
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
            'employer_name' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'years_in_line_of_work' => ['nullable', 'integer', 'min:0', 'max:100'],
            'monthly_income_cents' => ['nullable', 'integer', 'min:0'],
            'is_current' => ['boolean'],
        ];
    }
}
