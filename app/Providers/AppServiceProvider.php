<?php

namespace App\Providers;

use App\Models\Loan;
use App\Observers\LoanObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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
        Loan::observe(LoanObserver::class);

        // ── Rate Limiting ────────────────────────────────────────────────────
        // Login endpoint: 5 dakikada max 10 deneme (email + IP bazlı)
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinutes(5, 10)
                ->by(($request->input('email') ?: '') . '|' . $request->ip());
        });
    }
}
