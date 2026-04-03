<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Borrower\UpdateBorrowerRequest;
use App\Http\Resources\BorrowerResource;
use App\Models\Borrower;
use Illuminate\Http\JsonResponse;

class BorrowerController extends Controller
{
    public function show(Borrower $borrower): BorrowerResource
    {
        $this->authorize('view', $borrower);

        $borrower->load(['identity', 'employments', 'assets', 'declaration']);

        return new BorrowerResource($borrower);
    }

    public function update(UpdateBorrowerRequest $request, Borrower $borrower): BorrowerResource
    {
        $borrower->update($request->validated());

        $borrower->refresh()->load(['identity', 'employments', 'assets', 'declaration']);

        return new BorrowerResource($borrower);
    }

    public function destroy(Borrower $borrower): JsonResponse
    {
        $this->authorize('delete', $borrower);

        $borrower->delete();

        return response()->json(['message' => __('Borrower deleted.')]);
    }
}
