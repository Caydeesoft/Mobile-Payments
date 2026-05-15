<?php

namespace Caydeesoft\Payments\Callbacks;

use Caydeesoft\Payments\Traits\Helper;
use Illuminate\Http\Request;

class GenericCallback implements CallbackInterface
{
    use Helper;

    public function processB2BRequestCallback(Request $request)
    {
        return $this->payload($request, 'b2b');
    }

    public function processTransactionStatusRequestCallback(Request $request)
    {
        return $this->payload($request, 'transaction-status');
    }

    public function processSTKPushQueryRequestCallback(Request $request)
    {
        return $this->payload($request, 'stk-query');
    }

    public function processSTKPushRequestCallback(Request $request)
    {
        return $this->payload($request, 'stk');
    }

    public function processReversalRequestCallBack(Request $request)
    {
        return $this->payload($request, 'reversal');
    }

    public function processAccountBalanceRequestCallback(Request $request)
    {
        return $this->payload($request, 'account-balance');
    }

    public function processC2BRequestConfirmation(Request $request)
    {
        return $this->payload($request, 'c2b-confirmation');
    }

    public function C2BRequestValidation(Request $request)
    {
        return $this->payload($request, 'c2b-validation');
    }

    public function processB2CRequestCallback(Request $request)
    {
        return $this->payload($request, 'b2c');
    }

    protected function payload(Request $request, $event)
    {
        return [
            'provider' => $this->providerName(),
            'event' => $event,
            'headers' => $this->redactHeaders($request->headers->all()),
            'payload' => $this->redactArray($request->all()),
            'raw' => $request->getContent(),
        ];
    }

    protected function providerName()
    {
        $parts = explode('\\', static::class);

        return strtolower(end($parts));
    }
}
