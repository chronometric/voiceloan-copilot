<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Borrower\UpdateDeclarationRequest;
use App\Http\Resources\BorrowerDeclarationResource;
use App\Http\Resources\BorrowerResource;
use App\Models\Borrower;

class BorrowerDeclarationController extends Controller
{
    public function show(Borrower $borrower): BorrowerDeclarationResource
    {
        $this->authorize('view', $borrower);

        $declaration = $borrower->declaration()->firstOrCreate(['borrower_id' => $borrower->id], []);

        return new BorrowerDeclarationResource($declaration);
    }

    public function update(UpdateDeclarationRequest $request, Borrower $borrower): BorrowerResource
    {
        $declaration = $borrower->declaration()->firstOrCreate(['borrower_id' => $borrower->id], []);
        $declaration->update($request->validated());

        $borrower->refresh()->load(['identity', 'employments', 'assets', 'declaration']);

        return new BorrowerResource($borrower);
    }
}
