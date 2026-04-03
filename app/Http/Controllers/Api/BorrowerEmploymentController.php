<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Borrower\StoreEmploymentRequest;
use App\Http\Requests\Borrower\UpdateEmploymentRequest;
use App\Http\Resources\BorrowerEmploymentResource;
use App\Http\Resources\BorrowerResource;
use App\Models\Borrower;
use App\Models\BorrowerEmployment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BorrowerEmploymentController extends Controller
{
    public function index(Borrower $borrower): AnonymousResourceCollection
    {
        $this->authorize('view', $borrower);

        return BorrowerEmploymentResource::collection(
            $borrower->employments()->orderByDesc('id')->get(),
        );
    }

    public function store(StoreEmploymentRequest $request, Borrower $borrower): BorrowerResource
    {
        $borrower->employments()->create($request->validated());

        $borrower->refresh()->load(['identity', 'employments', 'assets', 'declaration']);

        return new BorrowerResource($borrower);
    }

    public function update(
        UpdateEmploymentRequest $request,
        Borrower $borrower,
        BorrowerEmployment $employment,
    ): BorrowerResource {
        abort_unless($employment->borrower_id === $borrower->id, 404);

        $employment->update($request->validated());

        $borrower->refresh()->load(['identity', 'employments', 'assets', 'declaration']);

        return new BorrowerResource($borrower);
    }

    public function destroy(Borrower $borrower, BorrowerEmployment $employment): JsonResponse
    {
        $this->authorize('update', $borrower);
        abort_unless($employment->borrower_id === $borrower->id, 404);

        $employment->delete();

        return response()->json(['message' => __('Employment removed.')]);
    }
}
