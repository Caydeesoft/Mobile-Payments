<?php

namespace Caydeesoft\Payments\Callbacks;

use Illuminate\Http\Request;

class Mtn implements CallbackInterface
{

    public function processB2BRequestCallback(Request $request)
    {
        // TODO: Implement processB2BRequestCallback() method.
    }

    public function processTransactionStatusRequestCallback(Request $request)
    {
        // TODO: Implement processTransactionStatusRequestCallback() method.
    }

    public function processSTKPushQueryRequestCallback(Request $request)
    {
        // TODO: Implement processSTKPushQueryRequestCallback() method.
    }

    public function processSTKPushRequestCallback(Request $request)
    {
        // TODO: Implement processSTKPushRequestCallback() method.
    }

    public function processReversalRequestCallBack(Request $request)
    {
        // TODO: Implement processReversalRequestCallBack() method.
    }

    public function processAccountBalanceRequestCallback(Request $request)
    {
        // TODO: Implement processAccountBalanceRequestCallback() method.
    }

    public function processC2BRequestConfirmation(Request $request)
    {
        // TODO: Implement processC2BRequestConfirmation() method.
    }

    public function C2BRequestValidation(Request $request)
    {
        // TODO: Implement C2BRequestValidation() method.
    }

    public function processB2CRequestCallback(Request $request)
    {
        // TODO: Implement processB2CRequestCallback() method.
    }
}