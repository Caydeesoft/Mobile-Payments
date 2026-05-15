<?php

namespace Caydeesoft\Payments;

use Caydeesoft\Payments\Http\Middleware\VerifyPaymentCallback;
use Caydeesoft\Payments\Libs\Payments;
use Illuminate\Routing\Router;
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
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('payments.callback.verify', VerifyPaymentCallback::class);

        if (config('payments.routes.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/routes/payments.php');
        }

        $this->publishes([
            __DIR__ . '/config/payments.php' => config_path('payments.php'),
        ], 'payments-config');
    }
}
