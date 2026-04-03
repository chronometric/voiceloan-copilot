<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\BorrowerAsset
 */
class BorrowerAssetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'asset_type' => $this->asset_type,
            'description' => $this->description,
            'value_cents' => $this->value_cents,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
