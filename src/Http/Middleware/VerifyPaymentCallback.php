<?php

namespace Caydeesoft\Payments\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

class VerifyPaymentCallback
{
    public function handle(Request $request, Closure $next)
    {
        if (! config('payments.callbacks.verification.enabled', false)) {
            return $next($request);
        }

        if (! $this->ipAllowed($request)) {
            return response()->json(['message' => 'Callback IP not allowed'], 403);
        }

        if (! $this->timestampValid($request)) {
            return response()->json(['message' => 'Callback timestamp invalid'], 401);
        }

        if (! $this->secretValid($request)) {
            return response()->json(['message' => 'Callback signature invalid'], 401);
        }

        return $next($request);
    }

    protected function ipAllowed(Request $request)
    {
        $allowed = config('payments.callbacks.verification.allowed_ips', []);

        return empty($allowed) || IpUtils::checkIp($request->ip(), $allowed);
    }

    protected function timestampValid(Request $request)
    {
        $header = config('payments.callbacks.verification.timestamp_header', 'X-Payments-Timestamp');
        $timestamp = $request->header($header);

        if (! $timestamp) {
            return true;
        }

        return abs(time() - (int) $timestamp) <= (int) config('payments.callbacks.verification.tolerance', 300);
    }

    protected function secretValid(Request $request)
    {
        $secret = config('payments.callbacks.verification.secret');

        if (! $secret) {
            return true;
        }

        $header = config('payments.callbacks.verification.secret_header', 'X-Payments-Signature');
        $provided = (string) $request->header($header, '');
        $timestamp = (string) $request->header(config('payments.callbacks.verification.timestamp_header', 'X-Payments-Timestamp'), '');
        $expected = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);

        return hash_equals($expected, $provided) || hash_equals($secret, $provided);
    }
}
