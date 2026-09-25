<?php

namespace App\Providers;

use App\Services\Settings\Auth\RoleSettingService;
use App\Services\Settings\Margins\MarginsService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // These services keep small, request-local lookup caches. A scoped
        // lifetime prevents repeated authorization and margin queries while
        // remaining safe for long-running queue / Octane workers.
        $this->app->scoped(RoleSettingService::class);
        $this->app->scoped(MarginsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
