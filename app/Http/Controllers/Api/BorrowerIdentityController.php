<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Borrower\UpdateIdentityRequest;
use App\Http\Resources\BorrowerIdentityResource;
use App\Http\Resources\BorrowerResource;
use App\Models\Borrower;

class BorrowerIdentityController extends Controller
{
    public function show(Borrower $borrower): BorrowerIdentityResource
    {
        $this->authorize('view', $borrower);

        $identity = $borrower->identity()->firstOrCreate(['borrower_id' => $borrower->id], []);

        return new BorrowerIdentityResource($identity);
    }

    public function update(UpdateIdentityRequest $request, Borrower $borrower): BorrowerResource
    {
        $identity = $borrower->identity()->firstOrCreate(['borrower_id' => $borrower->id], []);
        $identity->update($request->validated());

        $borrower->refresh()->load(['identity', 'employments', 'assets', 'declaration']);

        return new BorrowerResource($borrower);
    }
}
