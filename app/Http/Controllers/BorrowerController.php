<?php

namespace App\Http\Controllers;

use App\Http\Requests\Borrower\StoreAssetRequest;
use App\Http\Requests\Borrower\StoreBorrowerRequest;
use App\Http\Requests\Borrower\StoreEmploymentRequest;
use App\Http\Requests\Borrower\UpdateAssetRequest;
use App\Http\Requests\Borrower\UpdateBorrowerRequest;
use App\Http\Requests\Borrower\UpdateDeclarationRequest;
use App\Http\Requests\Borrower\UpdateEmploymentRequest;
use App\Http\Requests\Borrower\UpdateIdentityRequest;
use App\Models\Borrower;
use App\Models\BorrowerAsset;
use App\Models\BorrowerEmployment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BorrowerController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Borrower::class);

        $borrowers = auth()->user()->borrowers()->latest()->paginate(15);

        return view('borrowers.index', compact('borrowers'));
    }

    public function create(): View
    {
        $this->authorize('create', Borrower::class);

        return view('borrowers.create');
    }

    public function store(StoreBorrowerRequest $request): RedirectResponse
    {
        $borrower = $request->user()->borrowers()->create($request->validated());

        return redirect()
            ->route('borrowers.edit', $borrower)
            ->with('status', __('Borrower created.'));
    }

    public function edit(Request $request, Borrower $borrower): View
    {
        $this->authorize('update', $borrower);

        $borrower->identity()->firstOrCreate(
            ['borrower_id' => $borrower->id],
            [],
        );
        $borrower->declaration()->firstOrCreate(
            ['borrower_id' => $borrower->id],
            [],
        );

        $borrower->load(['identity', 'employments', 'assets', 'declaration']);
        $auditLogs = $borrower->auditLogs()->with('user')->latest()->limit(50)->get();
        $tab = $request->query('tab', 'main');

        return view('borrowers.edit', [
            'borrower' => $borrower,
            'auditLogs' => $auditLogs,
            'tab' => $tab,
        ]);
    }

    public function update(UpdateBorrowerRequest $request, Borrower $borrower): RedirectResponse
    {
        $borrower->update($request->validated());

        return redirect()
            ->to(route('borrowers.edit', $borrower).'?tab=main')
            ->with('status', __('Saved.'));
    }

    public function updateIdentity(UpdateIdentityRequest $request, Borrower $borrower): RedirectResponse
    {
        $identity = $borrower->identity()->firstOrCreate(['borrower_id' => $borrower->id], []);
        $identity->update($request->validated());

        return redirect()
            ->to(route('borrowers.edit', $borrower).'?tab=identity')
            ->with('status', __('Identity saved.'));
    }

    public function updateDeclaration(UpdateDeclarationRequest $request, Borrower $borrower): RedirectResponse
    {
        $declaration = $borrower->declaration()->firstOrCreate(['borrower_id' => $borrower->id], []);
        $declaration->update($request->validated());

        return redirect()
            ->to(route('borrowers.edit', $borrower).'?tab=declarations')
            ->with('status', __('Declarations saved.'));
    }

    public function storeEmployment(StoreEmploymentRequest $request, Borrower $borrower): RedirectResponse
    {
        $borrower->employments()->create($request->validated());

        return redirect()
            ->to(route('borrowers.edit', $borrower).'?tab=employment')
            ->with('status', __('Employment added.'));
    }

    public function updateEmployment(
        UpdateEmploymentRequest $request,
        Borrower $borrower,
        BorrowerEmployment $employment,
    ): RedirectResponse {
        abort_unless($employment->borrower_id === $borrower->id, 404);

        $employment->update($request->validated());

        return redirect()
            ->to(route('borrowers.edit', $borrower).'?tab=employment')
            ->with('status', __('Employment updated.'));
    }

    public function destroyEmployment(Borrower $borrower, BorrowerEmployment $employment): RedirectResponse
    {
        $this->authorize('update', $borrower);
        abort_unless($employment->borrower_id === $borrower->id, 404);

        $employment->delete();

        return redirect()
            ->to(route('borrowers.edit', $borrower).'?tab=employment')
            ->with('status', __('Employment removed.'));
    }

    public function storeAsset(StoreAssetRequest $request, Borrower $borrower): RedirectResponse
    {
        $borrower->assets()->create($request->validated());

        return redirect()
            ->to(route('borrowers.edit', $borrower).'?tab=assets')
            ->with('status', __('Asset added.'));
    }

    public function updateAsset(
        UpdateAssetRequest $request,
        Borrower $borrower,
        BorrowerAsset $asset,
    ): RedirectResponse {
        abort_unless($asset->borrower_id === $borrower->id, 404);

        $asset->update($request->validated());

        return redirect()
            ->to(route('borrowers.edit', $borrower).'?tab=assets')
            ->with('status', __('Asset updated.'));
    }

    public function destroyAsset(Borrower $borrower, BorrowerAsset $asset): RedirectResponse
    {
        $this->authorize('update', $borrower);
        abort_unless($asset->borrower_id === $borrower->id, 404);

        $asset->delete();

        return redirect()
            ->to(route('borrowers.edit', $borrower).'?tab=assets')
            ->with('status', __('Asset removed.'));
    }

    public function destroy(Borrower $borrower): RedirectResponse
    {
        $this->authorize('delete', $borrower);

        $borrower->delete();

        return redirect()
            ->route('borrowers.index')
            ->with('status', __('Borrower deleted.'));
    }
}
