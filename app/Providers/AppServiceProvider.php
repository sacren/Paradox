<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Illuminate\Support\Facades\Session;

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
        Inertia::share([
            'flash' => fn () => [
                'success' => Session::get('success') ?? null,
                'error' => Session::get('error') ?? null,
                'warning' => Session::get('warning') ?? null,
                'info' => Session::get('info') ?? null,
            ],
        ]);
    }
}
