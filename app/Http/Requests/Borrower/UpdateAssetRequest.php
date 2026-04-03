<?php

namespace App\Http\Requests\Borrower;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssetRequest extends FormRequest
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
            'asset_type' => ['sometimes', 'nullable', 'string', 'max:64'],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'value_cents' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }
}
