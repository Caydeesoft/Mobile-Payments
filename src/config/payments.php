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
        'verification' => [
            'enabled' => env('PAYMENTS_CALLBACK_VERIFICATION', false),
            'secret' => env('PAYMENTS_CALLBACK_SECRET'),
            'secret_header' => env('PAYMENTS_CALLBACK_SECRET_HEADER', 'X-Payments-Signature'),
            'timestamp_header' => env('PAYMENTS_CALLBACK_TIMESTAMP_HEADER', 'X-Payments-Timestamp'),
            'tolerance' => env('PAYMENTS_CALLBACK_TOLERANCE', 300),
            'allowed_ips' => array_filter(explode(',', env('PAYMENTS_CALLBACK_ALLOWED_IPS', ''))),
        ],
        'redact_headers' => [
            'authorization',
            'cookie',
            'set-cookie',
            'x-api-key',
            'x-auth-token',
            'x-payments-signature',
            'ocp-apim-subscription-key',
        ],
        'success_response' => [
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted',
        ],
        'mpesa_validator' => null,
        'mpesa_validation_fallback' => [
            'ResultCode' => '0',
            'ResultDesc' => 'Accepted',
        ],
    ],

    'http' => [
        'ca_bundle' => env('PAYMENTS_CA_BUNDLE'),
    ],

    'channels' => [
        'mpesa' => [
            'production_url' => env('MPESA_PRODUCTION_URL', 'https://api.safaricom.co.ke'),
            'sandbox_url' => env('MPESA_SANDBOX_URL', 'https://sandbox.safaricom.co.ke'),
            'credentials' => [
                'consumerkey' => env('MPESA_CONSUMER_KEY'),
                'consumersecret' => env('MPESA_CONSUMER_SECRET'),
                'passkey' => env('MPESA_PASSKEY'),
                'shortcode' => env('MPESA_SHORTCODE'),
                'initiator' => env('MPESA_INITIATOR'),
                'credential' => env('MPESA_SECURITY_CREDENTIAL'),
            ],
            'endpoints' => [
                'ratiba_create' => env('MPESA_RATIBA_CREATE_ENDPOINT', '/standingorder/v1/createStandingOrderExternal'),
                'ratiba_update' => env('MPESA_RATIBA_UPDATE_ENDPOINT', '/standingorder/v1/updateStandingOrderExternal'),
                'ratiba_cancel' => env('MPESA_RATIBA_CANCEL_ENDPOINT', '/standingorder/v1/cancelStandingOrderExternal'),
                'ratiba_query' => env('MPESA_RATIBA_QUERY_ENDPOINT', '/standingorder/v1/queryStandingOrderExternal'),
            ],
            'allowed_endpoints' => [],
        ],

        'airtel' => [
            'country' => env('AIRTEL_MONEY_COUNTRY', 'KE'),
            'currency' => env('AIRTEL_MONEY_CURRENCY', 'KES'),
            'production_url' => env('AIRTEL_MONEY_PRODUCTION_URL', 'https://openapi.airtel.africa'),
            'sandbox_url' => env('AIRTEL_MONEY_SANDBOX_URL', 'https://openapiuat.airtel.africa'),
            'credentials' => [
                'consumerkey' => env('AIRTEL_MONEY_CLIENT_ID'),
                'consumersecret' => env('AIRTEL_MONEY_CLIENT_SECRET'),
            ],
            'allowed_endpoints' => [],
        ],

        'eazzy' => [
            'production_url' => env('EAZZYPAY_PRODUCTION_URL', 'https://api.equitybankgroup.com'),
            'sandbox_url' => env('EAZZYPAY_SANDBOX_URL', 'https://api.equitybankgroup.com'),
            'credentials' => [
                'consumerkey' => env('EAZZYPAY_CONSUMER_KEY'),
                'consumersecret' => env('EAZZYPAY_CONSUMER_SECRET'),
                'username' => env('EAZZYPAY_USERNAME'),
                'password' => env('EAZZYPAY_PASSWORD'),
            ],
            'allowed_endpoints' => [],
        ],

        'tkash' => [
            'production_url' => env('TKASH_PRODUCTION_URL', 'https://production.gw.mfs-tkl.com'),
            'sandbox_url' => env('TKASH_SANDBOX_URL', 'https://staging.gw.mfs-tkl.com'),
            'credentials' => [
                'consumerkey' => env('TKASH_CONSUMER_KEY'),
                'consumersecret' => env('TKASH_CONSUMER_SECRET'),
                'username' => env('TKASH_USERNAME'),
                'password' => env('TKASH_PASSWORD'),
            ],
            'allowed_endpoints' => [],
        ],

        'mtn' => [
            'production_url' => env('MTN_MOMO_PRODUCTION_URL', 'https://momodeveloper.mtn.com'),
            'sandbox_url' => env('MTN_MOMO_SANDBOX_URL', 'https://sandbox.momodeveloper.mtn.com'),
            'environment' => env('MTN_MOMO_TARGET_ENVIRONMENT', 'sandbox'),
            'currency' => env('MTN_MOMO_CURRENCY', 'EUR'),
            'credentials' => [
                'api_user' => env('MTN_MOMO_API_USER'),
                'api_key' => env('MTN_MOMO_API_KEY'),
                'subscription_key' => env('MTN_MOMO_SUBSCRIPTION_KEY'),
                'collection_subscription_key' => env('MTN_MOMO_COLLECTION_SUBSCRIPTION_KEY'),
                'disbursement_subscription_key' => env('MTN_MOMO_DISBURSEMENT_SUBSCRIPTION_KEY'),
            ],
            'allowed_endpoints' => [],
        ],

        'tigo' => [
            'production_url' => env('TIGO_PESA_PRODUCTION_URL', 'https://secure.tigo.com'),
            'sandbox_url' => env('TIGO_PESA_SANDBOX_URL', 'https://secure.tigo.com'),
            'currency' => env('TIGO_PESA_CURRENCY', 'TZS'),
            'credentials' => [
                'consumerkey' => env('TIGO_PESA_CLIENT_ID'),
                'consumersecret' => env('TIGO_PESA_CLIENT_SECRET'),
                'username' => env('TIGO_PESA_USERNAME'),
                'password' => env('TIGO_PESA_PASSWORD'),
            ],
            'allowed_endpoints' => [],
        ],
    ],
];
