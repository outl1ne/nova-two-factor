<?php

namespace Outl1ne\NovaTwoFactor;

use Laravel\Nova\Nova;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Outl1ne\NovaTwoFactor\Http\Middleware\Authorize;

class TwoFactorServiceProvider extends ServiceProvider
{
    use LoadsNovaTranslations;

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadTranslations(__DIR__ . '/../lang', 'nova-two-factor', true);
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'nova-two-factor');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/nova-two-factor.php' => config_path('nova-two-factor.php'),
            ], 'nova-two-factor.config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations')
            ], 'migrations');

            $this->publishes([
                __DIR__ . '/../resources/lang' => lang_path('vendor/nova-two-factor')
            ], 'translations');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/nova-two-factor.php', 'nova-two-factor');
        $this->routes();
    }

    protected function routes()
    {
        if ($this->app->routesAreCached()) return;

        Nova::router(['nova', Authenticate::class, Authorize::class], 'nova-two-factor')
            ->group(__DIR__ . '/../routes/inertia.php');

        Route::middleware(['nova', Authorize::class])
            ->prefix('nova-vendor/nova-two-factor')
            ->group(__DIR__ . '/../routes/api.php');
    }
}
