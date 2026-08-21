<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use App\Http\View\Composers\LayoutComposer;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind public path to base path only on production (needed for shared hosting root index.php)
        // We also check HTTP_HOST in case APP_ENV is still set to 'local' on the server.
        $isProduction = !$this->app->isLocal() || 
                        (isset($_SERVER['HTTP_HOST']) && str_contains($_SERVER['HTTP_HOST'], 'proteksitanamanunila.site'));
                        
        if ($isProduction) {
            $this->app->usePublicPath(base_path());
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set Carbon locale ke bahasa Indonesia
        Carbon::setLocale(config('app.locale', 'id'));
        
        View::composer('layouts.app', LayoutComposer::class);

        // Register approval policy
        Gate::policy(\App\Models\SuratApproval::class, \App\Policies\SuratApprovalPolicy::class);

        // We'll use the spatie/laravel-permission package's built-in role/permission system
        // The package is configured in config/permission.php
    }
}
