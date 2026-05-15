<?php


namespace Caydeesoft\Payments\Constants;


class TkashParameters
    {
        public const token_link = '/oauth/token';
        public const registerurl = '/c2b/register-url';
        public const updateURL = '/c2b/register-url';
        public const replayNotification = '/notifications/replay';
        public const stkpush = '/stkpush';
        public const balance = '/balance';
        public const b2c = '/b2c';
        public const b2b = '/b2b';
        public const refund = '/refund';

        public static function c2bvalidationcallback()
        {
            return url('api/payments/callbacks/tkash/c2b-validation');
        }

        public static function c2bconfirmationcallback()
        {
            return url('api/payments/callbacks/tkash/c2b-confirmation');
        }
    }
