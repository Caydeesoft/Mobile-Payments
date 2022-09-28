<?php

namespace Caydeesoft\Payments;

use \Illuminate\Support\Facades\App;
use \Illuminate\Support\ServiceProvider;

class PaymentsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
        {
            App::bind('payment', \Caydeesoft\Payments\Libs\Payments::class);
            $this->mergeConfigFrom(
                __DIR__ . '/src/config/payments.php', 'payments'
            );
        }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/src/config/payments.php' => config_path('payments.php'),
        ]);

    }
}
