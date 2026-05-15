# Security Policy

## Reporting Vulnerabilities

Please report security issues privately by email:

`dennis.kiptoo@caydeesoft.com`

Do not open public issues containing credentials, callback payloads, access tokens, certificates, customer phone numbers, account numbers, or transaction identifiers.

## Supported Versions

Security fixes target the latest tagged release. If you are running an older release, upgrade before reporting unless the issue also affects the latest release.

## Production Hardening

Before using live payment flows:

- Set provider credentials in environment variables or `config/payments.php`, not request payloads.
- Enable callback verification with `PAYMENTS_CALLBACK_VERIFICATION=true`.
- Configure `PAYMENTS_CALLBACK_SECRET` and sign callbacks as `hash_hmac('sha256', timestamp + "." + raw_body, secret)`.
- Configure `PAYMENTS_CALLBACK_ALLOWED_IPS` when your provider publishes stable callback IP ranges.
- Keep `PAYMENTS_CALLBACK_TOLERANCE` low enough to reduce replay risk.
- Add provider-specific reconciliation before treating high-value payments as final.
- Configure `allowed_endpoints` before using `api()` or partner-specific endpoint overrides.

## Built-In Controls

- TLS verification is enabled by default using the system CA store.
- A bundled CA file is available with `PAYMENTS_CA_BUNDLE=package`.
- Callback verification middleware supports shared secrets, timestamp tolerance, replay reduction, and IP allowlists.
- Sensitive callback headers are redacted before normalized callback payloads are returned.
- Provider secrets can be read from config/env before request payloads.
- Generic `api()` endpoint overrides require `allowed_endpoints` entries.
- Provider HTTP failures throw `PaymentException` with redacted response bodies.

## Sensitive Fields

These values should never be logged or committed:

- `consumerkey`
- `consumersecret`
- `client_secret`
- `password`
- `pin`
- `credential`
- `passkey`
- `api_key`
- `subscription_key`
