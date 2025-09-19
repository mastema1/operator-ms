<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema; 
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;

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
        Schema::defaultStringLength(191);
        
        // Set default pagination view
        Paginator::defaultView('components.pagination');
        Paginator::defaultSimpleView('components.pagination');
        
        // Universal Opacity Decay Feature - Start Date Configuration
        
        // This date controls when the application begins its gradual transparency effect
        // Opacity decreases by 10% every 7 days, minimum 10% opacity
        View::share('opacityDecayStartDate', '2025-10-05');
    }
}
