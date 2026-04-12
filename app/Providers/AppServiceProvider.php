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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 2. Tambahkan kode ini untuk memaksa HTTPS di Railway
        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }
    }
}
