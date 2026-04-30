<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Transaction;
use App\Policies\CategoryPolicy;
use App\Policies\TransactionPolicy;
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
        // ── Explicit Policy Registration ─────────────────────────────────────
        // Mendaftarkan policy secara eksplisit (tidak bergantung pada auto-discovery).
        // Ini memastikan policy selalu terdaftar meskipun naming convention berubah.
        Gate::policy(Transaction::class, TransactionPolicy::class);
        Gate::policy(Category::class,    CategoryPolicy::class);
    }
}
