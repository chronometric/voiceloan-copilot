<?php

namespace App\Http\Requests\Borrower;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetRequest extends FormRequest
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
            'asset_type' => ['nullable', 'string', 'max:64'],
            'description' => ['nullable', 'string', 'max:500'],
            'value_cents' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
