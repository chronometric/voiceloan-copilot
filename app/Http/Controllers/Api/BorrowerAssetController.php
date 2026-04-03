<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Borrower\StoreAssetRequest;
use App\Http\Requests\Borrower\UpdateAssetRequest;
use App\Http\Resources\BorrowerAssetResource;
use App\Http\Resources\BorrowerResource;
use App\Models\Borrower;
use App\Models\BorrowerAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BorrowerAssetController extends Controller
{
    public function index(Borrower $borrower): AnonymousResourceCollection
    {
        $this->authorize('view', $borrower);

        return BorrowerAssetResource::collection(
            $borrower->assets()->orderByDesc('id')->get(),
        );
    }

    public function store(StoreAssetRequest $request, Borrower $borrower): BorrowerResource
    {
        $borrower->assets()->create($request->validated());

        $borrower->refresh()->load(['identity', 'employments', 'assets', 'declaration']);

        return new BorrowerResource($borrower);
    }

    public function update(
        UpdateAssetRequest $request,
        Borrower $borrower,
        BorrowerAsset $asset,
    ): BorrowerResource {
        abort_unless($asset->borrower_id === $borrower->id, 404);

        $asset->update($request->validated());

        $borrower->refresh()->load(['identity', 'employments', 'assets', 'declaration']);

        return new BorrowerResource($borrower);
    }

    public function destroy(Borrower $borrower, BorrowerAsset $asset): JsonResponse
    {
        $this->authorize('update', $borrower);
        abort_unless($asset->borrower_id === $borrower->id, 404);

        $asset->delete();

        return response()->json(['message' => __('Asset removed.')]);
    }
}
