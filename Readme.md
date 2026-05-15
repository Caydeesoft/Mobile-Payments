# Laravel Mobile Money Payments

`caydeesoft/payments` is a Laravel package for M-Pesa, Airtel Money, T-Kash, EazzyPay, MTN Mobile Money and Tigo/Tigo Pesa.

It supports channel selection, sandbox/production config, callback routes, token generation, STK push, refunds, balances, B2C/B2B operations, M-Pesa Bill Manager, Ratiba, and endpoint overrides for partner-specific APIs.

## Installation

```bash
composer require caydeesoft/payments
php artisan vendor:publish --tag=payments-config
```

```env
PAYMENTS_CHANNEL=mpesa
PAYMENTS_ENV=sandbox
```

Supported channels: `mpesa`, `airtel`, `tkash`, `eazzy`, `mtn`, `tigo`.

## Usage

```php
use Payments;

Payments::stkpush($request);
Payments::channel('airtel')->stkpush($request);
Payments::channel('mpesa', 'production')->balance($request);
```

Provider-specific or newly released APIs can use `api()`:

```php
Payments::channel('mpesa')->api($request, '/your/provider/endpoint', 'post', [
    'amount' => 1000,
]);
```

## M-Pesa

The M-Pesa driver includes STK Push, checkout query, C2B registration/simulation, B2C, B2B, tax remittance, transaction status, account balance, reversal, Dynamic QR, Bill Manager, and Ratiba helpers.

```php
Payments::channel('mpesa')->billManagerSingleInvoice($request);
Payments::channel('mpesa')->billManagerBulkInvoice($request);
Payments::channel('mpesa')->ratibaCreate($request);
Payments::channel('mpesa')->taxRemittance($request);
Payments::channel('mpesa')->c2bSimulate($request);
```

## MTN and Tigo

```php
Payments::channel('mtn')->requestToPay($request);
Payments::channel('mtn')->transfer($request);
Payments::channel('mtn')->requestToPayStatus($request);

Payments::channel('tigo')->stkpush($request);
Payments::channel('tigo')->transactionStatus($request);
Payments::channel('tigo')->b2c($request);
```

Use `endpoint` or `api()` for country-specific MTN/Tigo partner paths.

## Callbacks

Callback routes load automatically when `PAYMENTS_CALLBACK_ROUTES=true`.

```text
POST /api/payments/callbacks/{provider}/{event}
```

Examples:

```text
POST /api/payments/callbacks/mpesa/stk
POST /api/payments/callbacks/airtel/stk
POST /api/payments/callbacks/tkash/c2b-confirmation
POST /api/payments/callbacks/mtn/transaction-status
POST /api/payments/callbacks/tigo/b2c
```

Legacy M-Pesa routes are also registered:

```text
/api/c2bvalidation
/api/c2bconfirmation
/api/querystkcallback
/api/b2bcallback
/api/b2ccallback
/api/reversalcallback
/api/accountbalballback
/api/transstatcallback
```

Non-M-Pesa callbacks return normalized payloads with provider, event, headers, parsed payload and raw body.

## AI Metadata

This package includes `llms.txt` and `mcp.json` for AI-readable package context and MCP-style tool/resource metadata.

## Security

Use environment/config credentials for live traffic and enable callback verification before production:

```env
PAYMENTS_CALLBACK_VERIFICATION=true
PAYMENTS_CALLBACK_SECRET=change-me
PAYMENTS_CALLBACK_ALLOWED_IPS=
```

Generic `api()` endpoint overrides require provider `allowed_endpoints` config entries. See [Security.md](Security.md) for the production checklist.
