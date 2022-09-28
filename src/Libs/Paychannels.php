<?php

namespace Caydeesoft\Payments\Libs;

interface Paychannels
{


    /**
     * @param $request
     * @return mixed
     */
    public function RegisterURL($request);

    /**
     * @param $plaintext
     * @return mixed
     */
    public function cert_encrypt($plaintext);

    /**
     * @param $request
     * @return mixed
     */
    public function balance($request);

    /**
     * @param $request
     * @return mixed
     */
    public function stkpush($request);

    /**
     * @param $request
     * @return mixed
     */
    public function b2c($request);

    /**
     * @param $request
     * @return mixed
     */
    public function b2b($request);

    /**
     * @param $request
     * @return mixed
     */
    public function refund($request);

    /**
     * @param $request
     * @return mixed
     */
    public function generate_token($request);

}
