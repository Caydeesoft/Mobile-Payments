<?php

namespace Caydeesoft\Payments\Libs;

use Caydeesoft\Payments\Traits\Helper;

class Mtn implements Paychannels
{
    use Helper;
    /**
     * @inheritDoc
     */
    public function RegisterURL($request)
    {
        // TODO: Implement RegisterURL() method.
    }

    /**
     * @inheritDoc
     */
    public function cert_encrypt($plaintext)
    {
        // TODO: Implement cert_encrypt() method.
    }

    /**
     * @inheritDoc
     */
    public function balance($request)
    {
        // TODO: Implement balance() method.
    }

    /**
     * @inheritDoc
     */
    public function stkpush($request)
    {
        // TODO: Implement stkpush() method.
    }

    /**
     * @inheritDoc
     */
    public function b2c($request)
    {
        // TODO: Implement b2c() method.
    }

    /**
     * @inheritDoc
     */
    public function b2b($request)
    {
        // TODO: Implement b2b() method.
    }

    /**
     * @inheritDoc
     */
    public function refund($request)
    {
        // TODO: Implement refund() method.
    }

    /**
     * @inheritDoc
     */
    public function generate_token($request)
    {
        // TODO: Implement generate_token() method.
    }
}
