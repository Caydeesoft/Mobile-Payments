<?php

namespace Caydeesoft\Payments\Libs;

use Caydeesoft\Payments\Constants\EazzyPayParameters;
use Caydeesoft\Payments\Traits\Helper;

class EazzyPay extends EazzyPayParameters implements Paychannels
{
    use Helper;
    protected  $link;
    public function __construct($env='production')
    {
        if($env == 'production')
        {
            $this->link       = 'https://api.equitybankgroup.com';

        }
        else
        {
            $this->link       = 'https://api.equitybankgroup.com';
        }
    }
    /**
     * @param $request
     * @return mixed
     */
    public function RegisterURL($request)
    {
        // TODO: Implement RegisterURL() method.
    }

    /**
     * @param $plaintext
     * @return mixed
     */
    public function cert_encrypt($plaintext)
    {
        // TODO: Implement cert_encrypt() method.
    }

    /**
     * @param $request
     * @return mixed
     */
    public function balance($request)
    {
        // TODO: Implement balance() method.
    }

    /**
     * @param $request
     * @return mixed
     */
    public function stkpush($request)
    {
        // TODO: Implement stkpush() method.
    }

    /**
     * @param $request
     * @return mixed
     */
    public function b2c($request)
    {
        try
        {
            $data   =   [
                'transactionReference'  =>  $request->ref,
                'source'                =>  [
                    'senderName'=>$request->sender_name
                ],
                'destination'           =>  $request->des,
                'transfer'              =>  $request->trans
            ];
            $url    =   $this->link.EazzyPayParameters::disburse_link;
            $token  =   $this->generate_token($request);
            if(property_exists($token,'access_token'))
            {
                return $this->invoke_server($url,$data,$token->access_token);
            }
        }
        catch(HttpException $e)
        {
            Log::error($e->getMessage());
        }
    }

    /**
     * @param $request
     * @return mixed
     */
    public function b2b($request)
    {
        // TODO: Implement b2b() method.
    }

    /**
     * @param $request
     * @return mixed
     */
    public function refund($request)
    {
        // TODO: Implement refund() method.
    }

    /**
     * @param $request
     * @return mixed
     */
    public function generate_token($request)
    {
        $url    =   $this->link.EazzyPayParameters::identity_link;
        $header =   [
            'Content-Type'  =>  'application/x-www-form-urlencoded',
            'Authorization' =>  'Basic '.base64_encode("$request->consumerkey:$request->consumersecret")
        ];
        $data   =   [
            'username'  =>  $request->username,
            'password'  =>  $request->password,
            'grant_type'=>  'password'
        ];
        $rq     =   Http::withHeaders($header)
            ->post($url,$data);
        if($rq->successful())
        {
            return $rq->object();
        }
    }
    public function airtime($request)
    {
        $data   =   [
            'customer'  =>  [
                'mobileNumber'=>$request->phone
            ],
            'airtime'   =>  [
                'amount'    =>  $request->amount,
                'reference' =>  $request->ref,
                'telco'     =>  $request->provider
            ]
        ];

        $url    =   $this->link.EazzyPayParameters::airtime_link;
        $token  =   $this->generate_token($request);
        if(property_exists($token,'access_token'))
        {
            return $this->invoke_server($url,$data,$token->access_token);
        }
    }

}
