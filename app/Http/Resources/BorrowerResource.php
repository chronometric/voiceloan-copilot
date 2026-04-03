<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Borrower
 */
class BorrowerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'display_name' => $this->display_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'identity' => $this->whenLoaded('identity', fn () => $this->identity
                ? new BorrowerIdentityResource($this->identity)
                : null),
            'employments' => $this->whenLoaded('employments', fn () => BorrowerEmploymentResource::collection($this->employments)),
            'assets' => $this->whenLoaded('assets', fn () => BorrowerAssetResource::collection($this->assets)),
            'declaration' => $this->whenLoaded('declaration', fn () => $this->declaration
                ? new BorrowerDeclarationResource($this->declaration)
                : null),
        ];
    }
}
