<?php

	namespace Caydeesoft\Payments\Libs;

	use Caydeesoft\Payments\Constants\EazzyPayParameters;
	use Caydeesoft\Payments\Traits\Helper;
	use Illuminate\Support\Facades\Http;

	class EazzyPay extends EazzyPayParameters implements Paychannels
		{
			use Helper;
			protected $provider = 'eazzy';

			protected $link;

			public function __construct($env = 'production')
				{
					$this->link = rtrim($this->configValue(
						$env === 'production' ? 'payments.channels.eazzy.production_url' : 'payments.channels.eazzy.sandbox_url',
						'https://api.equitybankgroup.com'
					),                  '/');
				}

			public function RegisterURL($request)
				{
					return [
						'message'      => 'EazzyPay callback URLs are configured from the bank or partner portal.',
						'callback_url' => $this->requestValue($request, 'callback_url'),
					];
				}

			public function cert_encrypt($plaintext)
				{
					return base64_encode($plaintext);
				}

			public function balance($request)
				{
					return $this->authorized($request, 'get', $this->requestValue($request, 'endpoint', '/transaction/v1-sandbox/balance'));
				}

			public function stkpush($request)
				{
					return $this->authorized($request, 'post', $this->requestValue($request, 'endpoint', EazzyPayParameters::disburse_link), $this->requestData($request));
				}

			public function b2c($request)
				{
					return $this->authorized($request, 'post', EazzyPayParameters::disburse_link, [
						'transactionReference' => $this->requestValue($request, 'ref', $this->requestValue($request, 'transactionReference')),
						'source'               => [
							'senderName' => $this->requestValue($request, 'sender_name', $this->requestValue($request, 'senderName')),
						],
						'destination'          => $this->requestValue($request, 'des', $this->requestValue($request, 'destination')),
						'transfer'             => $this->requestValue($request, 'trans', $this->requestValue($request, 'transfer')),
					]);
				}

			public function b2b($request)
				{
					return $this->authorized($request, 'post', $this->requestValue($request, 'endpoint', EazzyPayParameters::disburse_link), $this->requestData($request));
				}

			public function refund($request)
				{
					return $this->authorized($request, 'post', $this->requestValue($request, 'endpoint', '/transaction/v1-sandbox/refund'), $this->requestData($request));
				}

			public function generate_token($request)
				{
					$response = Http::asForm()
					                ->withHeaders([
						                              'Authorization' => 'Basic ' . base64_encode($this->credentialValue('eazzy', $request, 'consumerkey') . ':' . $this->credentialValue('eazzy', $request, 'consumersecret')),
					                              ])
					                ->withOptions(['verify' => $this->caBundle(), 'http_errors' => false])
					                ->post($this->url(EazzyPayParameters::identity_link), [
						                'username'   => $this->credentialValue('eazzy', $request, 'username'),
						                'password'   => $this->credentialValue('eazzy', $request, 'password'),
						                'grant_type' => $this->requestValue($request, 'grant_type', 'password'),
					                ]);

					return $response->successful() ? $response->object() : null;
				}

			public function airtime($request)
				{
					return $this->authorized($request, 'post', EazzyPayParameters::airtime_link, [
						'customer' => [
							'mobileNumber' => $this->requestValue($request, 'phone', $this->requestValue($request, 'msisdn')),
						],
						'airtime'  => [
							'amount'    => $this->requestValue($request, 'amount'),
							'reference' => $this->requestValue($request, 'ref'),
							'telco'     => $this->requestValue($request, 'provider'),
						],
					]);
				}

			protected function authorized($request, $method, $endpoint, array $payload = [])
				{
					$this->assertAllowedEndpoint('eazzy', $endpoint);
					$token = $this->generate_token($request);

					return $this->jsonRequest($method, $this->url($endpoint), $payload, $token ? $token->access_token : null);
				}

			protected function url($endpoint)
				{
					return $this->link . '/' . ltrim($endpoint, '/');
				}
		}
