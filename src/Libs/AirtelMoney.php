<?php

	namespace Caydeesoft\Payments\Libs;

	use Caydeesoft\Payments\Constants\AirtelMoneyParameters;
	use Caydeesoft\Payments\Traits\Helper;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Http;

	class AirtelMoney implements Paychannels
		{
			use Helper;
			

			protected $baseurl;
			protected $country;
			protected $currency;

			public function __construct($env = 'production')
				{
					$this->provider = 'airtel';
					$this->baseurl = rtrim($this->configValue(
						$env === 'production' ? 'payments.channels.airtel.production_url' : 'payments.channels.airtel.sandbox_url',
						$env === 'production' ? 'https://openapi.airtel.africa' : 'https://openapiuat.airtel.africa'
					),                     '/');

					$this->country  = $this->configValue('payments.channels.airtel.country', AirtelMoneyParameters::country);
					$this->currency = $this->configValue('payments.channels.airtel.currency', AirtelMoneyParameters::currency);
				}

			public function generate_token($request)
				{
					$response = Http::withHeaders(['Content-Type' => 'application/json'])
					                ->withOptions(['verify' => $this->caBundle(), 'http_errors' => false])
					                ->post($this->url(AirtelMoneyParameters::tokenurl), [
						                'client_id'     => $this->credentialValue('airtel', $request, 'consumerkey', $this->requestValue($request, 'client_id')),
						                'client_secret' => $this->credentialValue('airtel', $request, 'consumersecret', $this->requestValue($request, 'client_secret')),
						                'grant_type'    => 'client_credentials',
					                ]);

					return $response->successful() ? $response->object() : null;
				}

			public function RegisterURL($request)
				{
					return [
						'message'      => 'Airtel callback URLs are configured from the Airtel portal or partner account.',
						'callback_url' => $this->requestValue($request, 'callback_url'),
					];
				}

			public function cert_encrypt($data)
				{
					$key   = $this->requestValue($data, 'public_key');
					$plain = is_string($data) ? $data : $this->requestValue($data, 'value', '');

					if (!$key)
						{
							return base64_encode($plain);
						}

					openssl_public_encrypt($plain, $encrypted, $key);

					return base64_encode($encrypted);
				}

			public function stkpush($request)
				{
					return $this->authorized($request, 'post', AirtelMoneyParameters::stk_url, [
						'reference'   => $this->requestValue($request, 'ref', $this->requestValue($request, 'reference')),
						'subscriber'  => [
							'country'  => $this->requestValue($request, 'country', $this->country),
							'currency' => $this->requestValue($request, 'currency', $this->currency),
							'msisdn'   => $this->requestValue($request, 'msisdn'),
						],
						'transaction' => [
							'amount'   => $this->requestValue($request, 'amount'),
							'country'  => $this->requestValue($request, 'country', $this->country),
							'currency' => $this->requestValue($request, 'currency', $this->currency),
							'id'       => $this->requestValue($request, 'id', $this->requestValue($request, 'ref')),
						],
					]);
				}

			public function refund($request)
				{
					return $this->authorized($request, 'post', AirtelMoneyParameters::refund_url, [
						'transaction' => [
							'airtel_money_id' => $this->requestValue($request, 'receipt', $this->requestValue($request, 'transaction_id')),
						],
					]);
				}

			public function transaction_enquiry($request, $id = null)
				{
					return $this->authorized($request, 'get', AirtelMoneyParameters::trans_enquiry . ($id ?: $this->requestValue($request, 'id')));
				}

			public function kyc($request, $msisdn = null)
				{
					return $this->authorized($request, 'get', AirtelMoneyParameters::kyc_url . ($msisdn ?: $this->requestValue($request, 'msisdn')));
				}

			public function balance($request)
				{
					return $this->authorized($request, 'get', AirtelMoneyParameters::balance);
				}

			public function disburse(Request $request)
				{
					return $this->b2c($request);
				}

			public function disburse_refund(Request $request)
				{
					return $this->authorized($request, 'post', AirtelMoneyParameters::disburse_ref_url, [
						'transaction' => [
							'airtel_money_id' => $this->requestValue($request, 'receipt', $this->requestValue($request, 'transaction_id')),
						],
					]);
				}

			public function disburse_enquiry(Request $request)
				{
					return $this->authorized($request, 'get', AirtelMoneyParameters::disburse_enquiry . $this->requestValue($request, 'id', ''));
				}

			public function b2b_validate(Request $request)
				{
					return $this->authorized($request, 'post', AirtelMoneyParameters::b2cvalidate, $this->requestData($request));
				}

			public function b2b_status(Request $request)
				{
					return $this->authorized($request, 'post', AirtelMoneyParameters::b2cstatus, $this->requestData($request));
				}

			public function b2b_credit(Request $request)
				{
					return $this->authorized($request, 'post', AirtelMoneyParameters::b2ccredit, $this->requestData($request));
				}

			public function b2b_refund(Request $request)
				{
					return $this->authorized($request, 'post', AirtelMoneyParameters::b2crefund, $this->requestData($request));
				}

			public function b2c($request)
				{
					return $this->authorized($request, 'post', AirtelMoneyParameters::disburse_url, [
						'payee'       => [
							'msisdn' => $this->requestValue($request, 'msisdn'),
						],
						'reference'   => $this->requestValue($request, 'ref', $this->requestValue($request, 'reference')),
						'pin'         => $this->requestValue($request, 'pin'),
						'transaction' => [
							'amount' => $this->requestValue($request, 'amount'),
							'id'     => $this->requestValue($request, 'id', $this->requestValue($request, 'ref')),
						],
					]);
				}

			public function b2b($request)
				{
					return $this->authorized($request, 'post', $this->requestValue($request, 'endpoint', AirtelMoneyParameters::b2ccredit), $this->requestData($request));
				}

			protected function authorized($request, $method, $endpoint, array $payload = [])
				{
					$this->assertAllowedEndpoint('airtel', $endpoint);
					$token = $this->generate_token($request);

					return $this->jsonRequest($method, $this->url($endpoint), $payload, $token ? $token->access_token : null, [
						'X-Country'  => $this->requestValue($request, 'country', $this->country),
						'X-Currency' => $this->requestValue($request, 'currency', $this->currency),
					]);
				}

			protected function url($endpoint)
				{
					return $this->baseurl . '/' . ltrim($endpoint, '/');
				}
		}
