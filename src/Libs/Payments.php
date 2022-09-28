<?php

namespace Caydeesoft\Payments\Libs;

class Payments
{
    public $channel;
    public function __construct(PayChannels $channel)
    {
        $this->channel = $channel;
    }

    /**
     * @param $request
     * @return void
     */
    public function generate_token($request)
    {
        return $this->channel->generate_token($request);
    }
    /**
     * @param $request
     * @return mixed
     */
    public function stkpush($request)
    {
        return $this->channel->stkpush($request);
    }

    /**
     * @param $request
     * @return void
     */
    public function registerURL($request)
    {
        return $this->channel->RegisterURL($request);
    }

    /**
     * @param $request
     * @return mixed
     */
    public function balance($request)
    {
        return $this->channel->balance($request);
    }
    public function refund($request)
    {
        return $this->channel->refund($request);
    }
}
