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
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->configureRateLimiting();

        Paginator::useBootstrapFive();

        Gate::policy(Borrower::class, BorrowerPolicy::class);

        Borrower::observe(BorrowerObserver::class);
        BorrowerIdentity::observe(BorrowerIdentityObserver::class);
        BorrowerEmployment::observe(BorrowerEmploymentObserver::class);
        BorrowerAsset::observe(BorrowerAssetObserver::class);
        BorrowerDeclaration::observe(BorrowerDeclarationObserver::class);
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $per = (int) config('rate_limits.api_per_minute', 60);

            return Limit::perMinute($per)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            $per = (int) config('rate_limits.login_per_minute', 10);

            return Limit::perMinute($per)->by($request->ip());
        });

        RateLimiter::for('voice', function (Request $request) {
            $per = (int) config('rate_limits.voice_per_minute', 180);
            $key = $request->ip().'|'.sha1((string) $request->header('X-Voice-Bridge-Key', ''));

            return Limit::perMinute($per)->by($key);
        });
    }
}
