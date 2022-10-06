<?php

namespace Devinweb\LaravelPaytabs;

use Devinweb\LaravelPaytabs\Console\BillingCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LaravelPaytabsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->registerRoutes();
        $this->registerPublishing();
    }

    protected function registerRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
        // ->namespace("Devinweb\LaravelPaytabs\Http\Controller")
            ->group(__DIR__ . '/../routes/api.php');
    }

    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/paytabs.php' => config_path('paytabs.php'),
            ], 'paytabs-config');
            if (!class_exists('CreateTransactionsTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_transactions_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_transactions_table.php'),
                ], 'paytabs-migrations');
            }
            $this->commands([
                BillingCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/paytabs.php', 'paytabs');

        // Register the main class to use with the facade
        $this->app->singleton('laravel-paytabs', function () {

            return new LaravelPaytabs();
        });
    }
}
