<?php
namespace Caydeesoft\Payments\Traits;

use Caydeesoft\Payments\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;


trait Helper
    {
        public function invoke_server($link,$dt,$token,$method='post')
        {
            if($method == 'post')
                $data       =   Http::withHeaders(['Content-Type:application/json'])
                                    ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
                                    ->withToken($token)
                                    ->post($link,$dt);
            else if($method == 'put')
                $data       =   Http::withHeaders(['Content-Type:application/json'])
                                    ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
                                    ->withToken($token)
                                    ->put($link,$dt);
            else if($method == 'get')
                $data       =   Http::withHeaders(['Content-Type:application/json'])
                                    ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
                                    ->withToken($token)
                                    ->get($link,$dt);

            if($data->successful())
                {
                    return $data->object();
                }
            throwException((new PaymentException())->notification());
        }
        public function msisdnFormatter(String $msisdn,$prefix=254,int $size=9) :String
            {
                return $prefix.substr($msisdn,-($size));
            }
    }
