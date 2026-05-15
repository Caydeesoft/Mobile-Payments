<?php

namespace Caydeesoft\Payments\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentException extends HttpException
{
    public function notification()
    {
        return new static(401, 'Notification Failed', null, []);
    }

    public static function providerError($status, $provider, $endpoint, $body = null)
    {
        $message = sprintf(
            '%s request failed [%s] at %s',
            ucfirst($provider),
            $status,
            $endpoint
        );

        if ($body) {
            $message .= ': '.substr((string) $body, 0, 500);
        }

        return new static((int) $status ?: 500, $message, null, []);
    }
}
