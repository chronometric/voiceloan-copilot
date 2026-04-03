<?php

namespace App\Providers;

use App\Models\Borrower;
use App\Models\BorrowerAsset;
use App\Models\BorrowerDeclaration;
use App\Models\BorrowerEmployment;
use App\Models\BorrowerIdentity;
use App\Observers\BorrowerAssetObserver;
use App\Observers\BorrowerDeclarationObserver;
use App\Observers\BorrowerEmploymentObserver;
use App\Observers\BorrowerIdentityObserver;
use App\Observers\BorrowerObserver;
use App\Policies\BorrowerPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Gate::policy(Borrower::class, BorrowerPolicy::class);

        Borrower::observe(BorrowerObserver::class);
        BorrowerIdentity::observe(BorrowerIdentityObserver::class);
        BorrowerEmployment::observe(BorrowerEmploymentObserver::class);
        BorrowerAsset::observe(BorrowerAssetObserver::class);
        BorrowerDeclaration::observe(BorrowerDeclarationObserver::class);
    }
}
