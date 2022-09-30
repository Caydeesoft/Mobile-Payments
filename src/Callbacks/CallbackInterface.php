<?php
namespace Caydeesoft\Payments\Callbacks;

use Illuminate\Http\Request;

interface CallbackInterface
    {
        public function processB2BRequestCallback(Request $request);
        public function processTransactionStatusRequestCallback(Request $request);
        public function processSTKPushQueryRequestCallback(Request $request);
        public function processSTKPushRequestCallback(Request $request);
        public function processReversalRequestCallBack(Request $request);
        public function processAccountBalanceRequestCallback(Request $request);
        public function processC2BRequestConfirmation(Request $request);
        public function C2BRequestValidation(Request $request);
        public function processB2CRequestCallback(Request $request);
    }