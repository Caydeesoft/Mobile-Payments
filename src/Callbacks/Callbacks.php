<?php
namespace Caydeesoft\Payments\Callbacks;

use Illuminate\Http\Request;

class Callbacks
    {
        public  $prov;
        public function __construct(CallbackInterface $provider)
            {
                $this->prov = $provider;
            }

        /**
         * @param Request $request
         * @return mixed
         */
        public function B2B(Request $request)
            {
                return $this->prov->processB2BRequestCallback($request);
            }
        public function TransactionStatus(Request $request)
            {
                return $this->prov->processTransactionStatusRequestCallback($request);
            }
        public function STKPushQuery(Request $request)
            {
                return $this->prov->processSTKPushQueryRequestCallback($request);
            }
        public function STKPush(Request $request)
            {
                return $this->prov->processSTKPushRequestCallback($request);
            }
        public function Reversal(Request $request)
            {
                return $this->prov->processReversalRequestCallBack($request);
            }
        public function AccountBalance(Request $request)
            {
                return $this->prov->processAccountBalanceRequestCallback($request) ;
            }
        public function C2BConfirmation(Request $request)
            {
                return $this->prov->processC2BRequestConfirmation($request);
            }
        public function C2BValidation(Request $request)
            {
                return $this->prov->C2BRequestValidation($request);
            }
        public function B2C(Request $request)
            {
                return $this->prov->processB2CRequestCallback($request);
            }
    }