# Laravel Mobile Money Payments for M-Pesa, Airtel Money, T-Kash, EazzyPay, MTN and Tigo

`caydeesoft/payments` is a Laravel mobile money payment gateway package for African payment providers. It supports M-Pesa STK Push and Daraja-style callbacks, Airtel Money collections and disbursements, T-Kash callback registration, EazzyPay transactions, MTN Mobile Money, and Tigo payment workflows.

Use it to add mobile payments, C2B collections, B2C payouts, B2B transfers, refunds, account balance requests, and payment callback handling to Laravel applications.

## Features

- Laravel facade and service provider auto-discovery.
- M-Pesa, Airtel Money, T-Kash, EazzyPay, MTN and Tigo channel selection.
- STK push, token generation, refunds, balance checks, B2C and B2B operations.
- Automatic callback routes for payment confirmations, validations and status updates.
- Configurable sandbox and production base URLs.
- Endpoint overrides for providers with partner-specific or unclear documentation.
- MCP-friendly metadata for AI agents and developer tools.

### Installation

`composer require caydeesoft/payments`

Publish the config file:

```bash
php artisan vendor:publish --tag=payments-config
```

Configure the default channel and environment:

```env
PAYMENTS_CHANNEL=mpesa
PAYMENTS_ENV=sandbox
```

Supported channels:

- `mpesa`
- `airtel`
- `tkash`
- `eazzy`
- `mtn`
- `tigo`

### Usage

Use the configured default channel:

```php
use Payments;

$response = Payments::stkpush($request);
```

Use a specific channel:

```php
use Payments;

$response = Payments::channel('airtel')->stkpush($request);
```

Use a specific channel environment:

```php
use Payments;

$response = Payments::channel('mpesa', 'production')->balance($request);
```

### M-Pesa APIs

The M-Pesa driver supports the core Daraja API surface:

- OAuth token generation.
- STK Push and checkout query.
- C2B URL registration and C2B simulation.
- B2C payment request.
- B2B payment request and tax remittance.
- Transaction status, account balance and reversal.
- Dynamic QR generation.
- Bill Manager opt-in, opt-in update, single invoice, bulk invoice, invoice cancellation, invoice query, payment query and reconciliation.
- Ratiba create, update, cancel, query and callback helper methods.

Examples:

```php
Payments::channel('mpesa')->billManagerSingleInvoice($request);
Payments::channel('mpesa')->billManagerBulkInvoice($request);
Payments::channel('mpesa')->ratibaCreate($request);
Payments::channel('mpesa')->taxRemittance($request);
Payments::channel('mpesa')->c2bSimulate($request);
```

For newly released Daraja 3.0 APIs or portal-specific paths, use the generic request helper:

```php
Payments::channel('mpesa')->api($request, '/your/daraja/endpoint', 'post', [
    'BusinessShortCode' => '123456',
]);
```

### MTN and Tigo

MTN Mobile Money and Tigo/Tigo Pesa are available through the same channel interface:

```php
Payments::channel('mtn')->stkpush($request);
Payments::channel('mtn')->requestToPayStatus($request);
Payments::channel('mtn')->transfer($request);

Payments::channel('tigo')->stkpush($request);
Payments::channel('tigo')->transactionStatus($request);
Payments::channel('tigo')->b2c($request);
```

Both drivers include a generic `api()` helper for country-specific or partner-specific endpoints:

```php
Payments::channel('tigo')->api($request, '/partner/payments/path', 'post', [
    'amount' => 1000,
    'msisdn' => '255700000000',
]);
```

Callbacks for Airtel Money, EazzyPay, T-Kash, MTN and Tigo return normalized payloads with provider, event, headers, parsed payload and raw body.

### Callback Routes

Callback routes are loaded automatically when `PAYMENTS_CALLBACK_ROUTES=true`.

Default callback pattern:

```text
POST /api/payments/callbacks/{provider}/{event}
```

Examples:

```text
POST /api/payments/callbacks/airtel/stk
POST /api/payments/callbacks/eazzy/b2c
POST /api/payments/callbacks/tkash/c2b-confirmation
```

Legacy M-Pesa callback URLs are also registered:

```text
POST /api/c2bvalidation
POST /api/c2bconfirmation
POST /api/querystkcallback
POST /api/b2bcallback
POST /api/b2ccallback
POST /api/reversalcallback
POST /api/accountbalballback
POST /api/transstatcallback
```

For Airtel Money, EazzyPay, and T-Kash, undocumented or partner-specific endpoints can be passed per request:

```php
$response = Payments::channel('tkash')->b2c((object) [
    'endpoint' => '/partner/specific/b2c/path',
    'consumerkey' => '...',
    'consumersecret' => '...',
    'username' => '...',
    'password' => '...',
    'amount' => 100,
    'msisdn' => '254700000000',
]);
```

### SEO and AI Metadata

This package includes:

- `llms.txt` for AI-readable package context.
- `mcp.json` for MCP-style tool and resource discovery metadata.
- SEO-friendly Composer keywords for Laravel payments, mobile money, M-Pesa, Airtel Money, T-Kash, EazzyPay, MTN and Tigo.

The MCP metadata is descriptive and safe by default: it documents tools, inputs and resources for agents without exposing secrets or invoking payment APIs automatically.
