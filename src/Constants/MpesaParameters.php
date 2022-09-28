<?php


namespace Caydeesoft\Payments\Constants;;


class MpesaParameters
    {
        public const token_link             =   '/oauth/v1/generate?grant_type=client_credentials';
        public const checkout_processlink   =   '/mpesa/stkpush/v1/processrequest';
        public const checkout_querylink     =   '/mpesa/stkpushquery/v1/query';
        public const reversal_link          =   '/mpesa/reversal/v1/request';
        public const balance_link           =   '/mpesa/accountbalance/v1/query';
        public const c2b_regiterUrl         =   '/mpesa/c2b/v1/registerurl';
        public const transtat_link          =   '/mpesa/transactionstatus/v1/query';
        public const b2b_link               =   '/mpesa/b2b/v1/paymentrequest';
        public const b2c_link               =   '/mpesa/b2c/v1/paymentrequest';
        public static function reversalURL()
            {
                return url("api/reversalcallback");
            }
        public static function accountbalcallback()
            {
                return url("api/accountbalballback");
            }
        public static function transtatURL()
            {
                return url("api/transstatcallback");
            }
        public static function b2bcallback()
            {
                return url("api/b2bcallback");
            }
        public static function b2ccallback()
            {
                return url("api/b2ccallback");
            }
        public static function stkquerycallback()
            {
                return url("api/querystkcallback");
            }
        public static function stkrequestcallback()
            {
                return url("api/querystkcallback");
            }
        public static function c2bvalidationcallback()
            {
                return  url("api/c2bvalidation");
            }
        public static function c2bconfirmationcallback()
            {
                return  url("api/c2bconfirmation");
            }
    }
