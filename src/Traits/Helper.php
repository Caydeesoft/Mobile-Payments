<?php
namespace Caydeesoft\Payments\Traits;

use Caydeesoft\Payments\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;


trait Helper
    {
        protected function resourcePath($file)
        {
            return dirname(__DIR__) . '/Resources/' . ltrim($file, '/');
        }

        protected function configValue($key, $default = null)
        {
            return function_exists('config') ? config($key, $default) : $default;
        }

        protected function requestValue($request, $key, $default = null)
        {
            if (is_array($request)) {
                return array_key_exists($key, $request) ? $request[$key] : $default;
            }

            return isset($request->{$key}) ? $request->{$key} : $default;
        }

        protected function requestData($request)
        {
            if (is_array($request)) {
                return $request;
            }

            if (method_exists($request, 'all')) {
                return $request->all();
            }

            return get_object_vars($request);
        }

        protected function jsonRequest($method, $url, array $payload = [], $token = null, array $headers = [])
        {
            $client = Http::withHeaders(array_merge(['Content-Type' => 'application/json'], $headers))
                ->withOptions(['verify' => $this->resourcePath('cacert.pem'), 'http_errors' => false]);

            if ($token) {
                $client = $client->withToken($token);
            }

            $response = strtolower($method) === 'get'
                ? $client->get($url, $payload)
                : $client->{strtolower($method)}($url, $payload);

            if ($response->successful()) {
                return $response->object();
            }

            throw new PaymentException($response->status(), $response->body());
        }

        public function invoke_server($link,$dt,$token,$method='post')
        {
            if($method == 'post')
                $data       =   Http::withHeaders(['Content-Type:application/json'])
                                    ->withOptions(['verify' => $this->resourcePath("cacert.pem"), 'http_errors' => false])
                                    ->withToken($token)
                                    ->post($link,$dt);
            else if($method == 'put')
                $data       =   Http::withHeaders(['Content-Type:application/json'])
                                    ->withOptions(['verify' => $this->resourcePath("cacert.pem"), 'http_errors' => false])
                                    ->withToken($token)
                                    ->put($link,$dt);
            else if($method == 'get')
                $data       =   Http::withHeaders(['Content-Type:application/json'])
                                    ->withOptions(['verify' => $this->resourcePath("cacert.pem"), 'http_errors' => false])
                                    ->withToken($token)
                                    ->get($link,$dt);

            if($data->successful())
                {
                    return $data->object();
                }
            throw (new PaymentException())->notification();
        }
        public function msisdnFormatter(String $msisdn,$prefix=254,int $size=9) :String
            {
                return $prefix.substr($msisdn,-($size));
            }
    }
