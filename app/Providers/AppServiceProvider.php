<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
        // Laravel's default pagination view uses Tailwind-styled icons.
        // Since this project uses Bootstrap, force Bootstrap 5 pagination
        // styling instead — this fixes the oversized/unstyled arrow icons.
        Paginator::useBootstrapFive();
    }
}