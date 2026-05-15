<?php

namespace Caydeesoft\Payments;

use Caydeesoft\Payments\Libs\Payments;
use Illuminate\Support\ServiceProvider;

class PaymentsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/payments.php', 'payments');

        $this->app->singleton('payment', function () {
            return new Payments(
                config('payments.default', 'mpesa'),
                config('payments.environment', 'sandbox')
            );
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('payments.routes.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/routes/payments.php');
        }

        $this->publishes([
            __DIR__ . '/config/payments.php' => config_path('payments.php'),
        ], 'payments-config');
    }
}
