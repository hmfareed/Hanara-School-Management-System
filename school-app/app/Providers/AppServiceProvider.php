<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('excel', function ($app) {
            return new \App\Services\SimpleExcel();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Excel', \Maatwebsite\Excel\Facades\Excel::class);
    }
}
