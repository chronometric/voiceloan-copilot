<?php

namespace App\Http\Requests\Borrower;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDeclarationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('borrower'));
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('additional_answers')) {
            return;
        }

        $value = $this->input('additional_answers');
        if (is_array($value)) {
            return;
        }
        if (! is_string($value)) {
            return;
        }

        $raw = trim($value);
        if ($raw === '') {
            $this->merge(['additional_answers' => null]);

            return;
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->merge(['additional_answers' => null]);
            $this->merge(['_additional_answers_invalid' => true]);

            return;
        }

        $this->merge(['additional_answers' => $decoded]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'outstanding_judgments' => ['sometimes', 'nullable', 'boolean'],
            'bankruptcy_past_seven_years' => ['sometimes', 'nullable', 'boolean'],
            'foreclosure_past_seven_years' => ['sometimes', 'nullable', 'boolean'],
            'party_to_lawsuit' => ['sometimes', 'nullable', 'boolean'],
            'obligated_on_loan_resulting_foreclosure' => ['sometimes', 'nullable', 'boolean'],
            'delinquent_on_federal_debt' => ['sometimes', 'nullable', 'boolean'],
            'additional_answers' => ['sometimes', 'nullable', 'array'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if ($this->boolean('_additional_answers_invalid')) {
                $v->errors()->add('additional_answers', __('Must be valid JSON.'));
            }
        });
    }
}
