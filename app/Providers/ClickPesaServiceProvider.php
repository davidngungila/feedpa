<?php

namespace App\Providers;

use App\Services\ClickPesaAPIService;
use Illuminate\Support\ServiceProvider;

class ClickPesaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/clickpesa.php',
            'clickpesa'
        );

        $this->app->singleton(ClickPesaAPIService::class, function ($app) {
            $config = [
                'api_base_url' => config('clickpesa.api_base_url'),
                'api_key' => config('clickpesa.api_key'),
                'client_id' => config('clickpesa.client_id'),
                'currency' => config('clickpesa.currency', 'TZS'),
            ];

            return new ClickPesaAPIService($config);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/clickpesa.php' => config_path('clickpesa.php'),
            ], 'clickpesa-config');
        }
    }
}
