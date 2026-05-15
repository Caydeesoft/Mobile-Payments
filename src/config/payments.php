<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Channel
    |--------------------------------------------------------------------------
    |
    | Supported: mpesa, airtel, tkash, eazzy, mtn, tigo
    |
    */
    'default' => env('PAYMENTS_CHANNEL', 'mpesa'),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | Use "sandbox" for test credentials and "production" for live traffic.
    |
    */
    'environment' => env('PAYMENTS_ENV', 'sandbox'),

    'routes' => [
        'enabled' => env('PAYMENTS_CALLBACK_ROUTES', true),
        'prefix' => env('PAYMENTS_CALLBACK_PREFIX', 'api'),
        'middleware' => ['api'],
    ],

    'callbacks' => [
        'success_response' => [
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted',
        ],
    ],

    'channels' => [
        'mpesa' => [
            'production_url' => env('MPESA_PRODUCTION_URL', 'https://api.safaricom.co.ke'),
            'sandbox_url' => env('MPESA_SANDBOX_URL', 'https://sandbox.safaricom.co.ke'),
            'endpoints' => [
                'ratiba_create' => env('MPESA_RATIBA_CREATE_ENDPOINT', '/standingorder/v1/createStandingOrderExternal'),
                'ratiba_update' => env('MPESA_RATIBA_UPDATE_ENDPOINT', '/standingorder/v1/updateStandingOrderExternal'),
                'ratiba_cancel' => env('MPESA_RATIBA_CANCEL_ENDPOINT', '/standingorder/v1/cancelStandingOrderExternal'),
                'ratiba_query' => env('MPESA_RATIBA_QUERY_ENDPOINT', '/standingorder/v1/queryStandingOrderExternal'),
            ],
        ],

        'airtel' => [
            'country' => env('AIRTEL_MONEY_COUNTRY', 'KE'),
            'currency' => env('AIRTEL_MONEY_CURRENCY', 'KES'),
            'production_url' => env('AIRTEL_MONEY_PRODUCTION_URL', 'https://openapi.airtel.africa'),
            'sandbox_url' => env('AIRTEL_MONEY_SANDBOX_URL', 'https://openapiuat.airtel.africa'),
        ],

        'eazzy' => [
            'production_url' => env('EAZZYPAY_PRODUCTION_URL', 'https://api.equitybankgroup.com'),
            'sandbox_url' => env('EAZZYPAY_SANDBOX_URL', 'https://api.equitybankgroup.com'),
        ],

        'tkash' => [
            'production_url' => env('TKASH_PRODUCTION_URL', 'https://production.gw.mfs-tkl.com'),
            'sandbox_url' => env('TKASH_SANDBOX_URL', 'https://staging.gw.mfs-tkl.com'),
        ],

        'mtn' => [
            'production_url' => env('MTN_MOMO_PRODUCTION_URL', 'https://momodeveloper.mtn.com'),
            'sandbox_url' => env('MTN_MOMO_SANDBOX_URL', 'https://sandbox.momodeveloper.mtn.com'),
            'environment' => env('MTN_MOMO_TARGET_ENVIRONMENT', 'sandbox'),
            'currency' => env('MTN_MOMO_CURRENCY', 'EUR'),
        ],

        'tigo' => [
            'production_url' => env('TIGO_PESA_PRODUCTION_URL', 'https://secure.tigo.com'),
            'sandbox_url' => env('TIGO_PESA_SANDBOX_URL', 'https://secure.tigo.com'),
            'currency' => env('TIGO_PESA_CURRENCY', 'TZS'),
        ],
    ],
];
