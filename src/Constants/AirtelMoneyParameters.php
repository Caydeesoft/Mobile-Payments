<?php


namespace Caydeesoft\Payments\Constants;


class AirtelMoneyParameters
    {
        public const country            =   'KE';
        public const currency           =   'KES';
        public const client_id          =   'b03039d1-83e8-4fb2-9d4d-65b17554d59c';
        public const client_secret      =   '';

        public const tokenurl           =   '/auth/oauth2/token';
        public const stk_url            =   '/merchant/v1/payments/';
        public const refund_url         =   '/standard/v1/payments/refund';
        public const trans_enquiry      =   '/standard/v1/payments/';
        public const kyc_url            =   '/standard/v1/users/';
        public const balance            =   '/standard/v1/users/balance';

        public const disburse_url       =   '/standard/v1/disbursements/';
        public const disburse_ref_url   =   '/standard/v1/disbursements/refund';
        public const disburse_enquiry   =   '/standard/v1/disbursements/';

        public const b2cvalidate        =   '/openapi/moneytransfer/v2/validate';
        public const b2cstatus          =   '/openapi/moneytransfer/v2/checkstatus';
        public const b2ccredit          =   '/openapi/moneytransfer/v2/credit';
        public const b2crefund          =   '/openapi/moneytransfer/v2/refund';
    }
