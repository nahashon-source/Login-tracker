<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;



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
        //
        HeadingRowFormatter::default('none');

        Paginator::useBootstrapFive(); // ✅ Forces Bootstrap 5 pagination views

    }
}
