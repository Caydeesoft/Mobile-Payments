<?php


namespace Caydeesoft\Payments\Libs;


use Caydeesoft\Payments\Constants\AirtelMoneyParameters;
use Caydeesoft\Payments\Traits\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AirtelMoney implements Paychannels
    {
    use Helper;
    public $baseurl;

    public function __construct ($env)
    {
        if ( $env == 'production' )
        {
            $this -> baseurl = 'https://openapi.airtel.africa/';
        }
        else
        {
            $this -> baseurl = 'https://openapiuat.airtel.africa/';
        }
    }

    public function generate_token($request)
    {
        try
        {
            $dt     =   [
                "client_id"     => $request->consumerkey ,
                "client_secret" => $request->consumersecret ,
                "grant_type" => "client_credentials"
            ];
            $data   =   Http ::withHeaders ( [ 'Content-Type' => 'application/json' ] )
                ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
                ->post ( $this -> baseurl . AirtelMoneyParameters::tokenurl ,$dt );
            if ( $data -> successful () )
            {
                return json_decode ( $data -> body () );
            }
        }
        catch(HttpException $e)
        {
            Log::error($e->getMessage());
        }


    }
    public function RegisterURL($request)
    {
        try
        {

        }
        catch(HttpException $e)
        {
            Log::error($e->getMessage()) ;
        }
    }
    public function cert_encrypt ($data)
    {
        try
        {
            $publicKeyString = file_get_contents ( app_path ( "/Resources/cacert.pem" ) );
            $publicKey       = openssl_pkey_get_public ( array ( $publicKeyString , "" ) );
            if ( ! $publicKey )
            {
                Log ::error ( "Public key NOT Correct" );
            }
            if ( ! openssl_public_encrypt ( $data , $encryptedWithPublic , $publicKey ) )
            {
                Log ::error ( "Error encrypting with public key" );
            }
            return base64_encode ( $encryptedWithPublic );
        }
        catch(\HttpException $e)
        {
            Log::error($e->getMessage()) ;
        }

    }

    public function stkpush ($request)
    {
        $data = Http ::withToken ( $this -> generate_token($request) -> access_token )
            ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
            -> withHeaders ( [ 'Content-Type' => 'application/json' , 'X-Country' => AirtelMoneyParameters::country , 'X-Currency' => AirtelMoneyParameters::currency ] )
            -> post ( $this -> baseurl . AirtelMoneyParameters::stk_url ,[ "reference" => $request -> ref , "subscriber" => [ "country" => AirtelMoneyParameters::country , "currency" => AirtelMoneyParameters::currency , "msisdn" => $request -> msisdn ] , "transaction" => [ "amount" => $request -> amount , "country" => AirtelMoneyParameters::country , "currency" => AirtelMoneyParameters::currency , "id" => $request -> id ] ] );
        if ( $data -> successful () )
        {
            return json_decode ( $data -> body () );
        }
        Log ::error ( $data -> clientError () );
    }

    public function refund ($request)
    {
        $data = Http ::withToken ( $this -> generate_token($request) -> access_token )
            ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
            -> withHeaders ( [ 'Content-Type' => 'application/json' , 'X-Country' => AirtelMoneyParameters::country , 'X-Currency' => AirtelMoneyParameters::currency ] )
            -> post ( $this -> baseurl . AirtelMoneyParameters::refund_url ,[ "transaction" => [ "airtel_money_id" => $request -> receipt ] ] );
        if ( $data -> successful () )
        {
            return json_decode ( $data -> body () );
        }
        Log ::error ( $data -> clientError () );
    }

    public function transaction_enquiry ($request,$id)
    {
        $data = Http ::withToken ( $this -> generate_token($request) -> access_token )
            ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
            -> withHeaders ( [ 'Content-Type' => 'application/json' , 'X-Country' => AirtelMoneyParameters::country , 'X-Currency' => AirtelMoneyParameters::currency ] )
            -> get ( $this -> baseurl . AirtelMoneyParameters::trans_enquiry . $id );
        if ( $data -> successful () )
        {
            return json_decode ( $data -> body () );
        }
        Log ::error ( $data -> clientError () );
    }

    public function kyc ($request,$msisdn)
    {
        $data = Http ::withToken ( $this -> generate_token($request) -> access_token )
            ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
            -> withHeaders ( [ 'Content-Type' => 'application/json' , 'X-Country' => AirtelMoneyParameters::country , 'X-Currency' => AirtelMoneyParameters::currency ] )
            -> get ( $this -> baseurl . AirtelMoneyParameters::kyc_url . $msisdn );
        if ( $data -> successful () )
        {
            return json_decode ( $data -> body () );
        }
        Log ::error ( $data -> clientError () );
    }

    public function balance ($request)
    {
        $data = Http ::withToken ( $this -> generate_token($request) -> access_token )
            ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
            -> withHeaders ( [ 'Content-Type' => 'application/json' , 'X-Country' => AirtelMoneyParameters::country , 'X-Currency' => AirtelMoneyParameters::currency ] )
            -> get ( $this -> baseurl . AirtelMoneyParameters::balance );
        if ( $data -> successful () )
        {
            return json_decode ( $data -> body () );
        }
        Log ::error ( $data -> clientError () );
    }

    public function disburse (Request $request)
    {
        $data = Http ::withToken ( $this -> generate_token($request) -> access_token )
            ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
            -> withHeaders ( [ 'Content-Type' => 'application/json' , 'X-Country' => AirtelMoneyParameters::country , 'X-Currency' => AirtelMoneyParameters::currency ] )
            -> post ( $this -> baseurl . AirtelMoneyParameters::disburse_url , [ "payee" => [ "msisdn" => $request -> msisdn ] , "reference" => $request -> ref , "pin" => $this -> certencrypt ( $request -> pin ) , "transaction" => [ "amount" => $request -> amount , "id" => $request -> id ] ] );
        if ( $data -> successful () )
        {
            return json_decode ( $data -> body () );
        }
        Log ::error ( $data -> clientError () );
    }

    public function disburse_refund (Request $request)
    {
        $data = Http ::withToken ( $this -> generate_token($request) -> access_token )
            ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
            -> withHeaders ( [ 'Content-Type' => 'application/json' , 'X-Country' => AirtelMoneyParameters::country , 'X-Currency' => AirtelMoneyParameters::currency ] )
            -> post ( $this -> baseurl . AirtelMoneyParameters::disburse_ref_url ,[ "transaction" => [ "airtel_money_id" => $request -> receipt ] ] );

        if ( $data -> successful () )
        {
            return json_decode ( $data -> body () );
        }
        Log ::error ( $data -> clientError () );
    }

    public function disburse_enquiry (Request $request)
    {
        $data = Http ::withToken ( $this -> generate_token($request) -> access_token )
            ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
            -> withHeaders ( [ 'Content-Type' => 'application/json' , 'X-Country' => AirtelMoneyParameters::country , 'X-Currency' => AirtelMoneyParameters::currency ] )
            -> get ( $this -> baseurl . AirtelMoneyParameters::disburse_enquiry );

        if ( $data -> successful () )
        {
            return json_decode ( $data -> body () );
        }
        Log ::error ( $data -> clientError () );
    }

    public function b2b_validate (Request $request)
    {
        $data = Http ::withToken ( $this -> generate_token($request) -> access_token )
            -> withHeaders ( [ 'Content-Type' => 'application/json' ] )
            ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
            -> post ( $this -> baseurl . AirtelMoneyParameters::b2cvalidate , [ "amount" => $request -> amount , "channelName" => $request -> channel , "country" => $request -> country , "currency" => $request -> currency , "msisdn" => $request -> msisdn ] );

        if ( $data -> successful () )
        {
            return json_decode ( $data -> body () );
        }
        Log ::error ( $data -> clientError () );
    }

    public function b2b_status (Request $request)
    {
        $data = Http ::withToken ( $this -> generate_token($request) -> access_token )
            ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
            -> withHeaders ( [ 'Content-Type' => 'application/json' ] )
            -> post ( $this -> baseurl . AirtelMoneyParameters::b2cstatus ,[ "channelName" => $request -> channel , "country" => $request -> country , "extTRID" => $request -> id ] );

        if ( $data -> successful () )
        {
            return json_decode ( $data -> body () );
        }
        Log ::error ( $data -> clientError () );
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function b2b_credit (Request $request)
    {
        $data = Http ::withToken ( $this -> generate_token($request) -> access_token )
            ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
            -> withHeaders ( [ 'Content-Type' => 'application/json' ] )
            -> post ( $this -> baseurl . AirtelMoneyParameters::b2ccredit , [ "amount" => $request -> amount , "channelName" => $request -> channel , "country" => $request -> country , "currency" => $request -> currency , "extTRID" => $request -> id , "msisdn" => $request -> msisdn , "mtcn" => $request -> mtcn , "payerCountry" => $request -> payer_country , "payerFirstName" => $request -> payer_firstname , "payerLastName" => $request -> payer_lastname , "pin" => $this -> certencrypt ( $request -> pin ) ] );

        if ( $data -> successful () )
        {
            return json_decode ( $data -> body () );
        }
        Log ::error ( $data -> clientError () );

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function b2b_refund (Request $request)
    {
        $data = Http ::withToken ( $this -> generate_token($request) -> access_token )
            ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
            -> withHeaders ( [ 'Content-Type' => 'application/json' ] )
            -> post ( $this -> baseurl . AirtelMoneyParameters::b2crefund , [ "channelName" => $request -> channel , "country" => $request -> country , "txnID" => $request -> txnID , "pin" => $this -> certencrypt ( $request -> pin ) ] );

        if ( $data -> successful () )
        {
            return json_decode ( $data -> body () );
        }
        Log ::error ( $data -> clientError () );
    }

    public function b2c($request)
    {
        try
        {

        }
        catch(HttpException $e)
        {
            Log::error($e->getMessage()) ;
        }
    }

    public function b2b($request)
    {
        try
        {

        }
        catch(HttpException $e)
        {
            Log::error($e->getMessage()) ;
        }
    }
    }
