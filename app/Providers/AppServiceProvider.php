<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Wajib ada untuk memanggil HTTPS

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
        // Paksa semua URL menggunakan HTTPS jika tidak di komputer lokal
        // Menggunakan config() lebih aman dari env() saat proses build Railway
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }
    }
}