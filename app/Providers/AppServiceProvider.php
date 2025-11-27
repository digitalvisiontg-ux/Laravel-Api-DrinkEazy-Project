<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Categorie;

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
        // Ensure the 'Promotion' category exists on app boot
        try {
            Categorie::firstOrCreate(['nomCat' => 'Promotion']);
        } catch (\Exception $e) {
            // Avoid breaking the app if DB is not available during some console operations
        }
    }
}
