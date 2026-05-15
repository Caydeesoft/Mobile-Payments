<?php

	namespace Caydeesoft\Payments\Libs;

	use Caydeesoft\Payments\Traits\Helper;
	use Illuminate\Support\Facades\Http;

	class Tigo implements Paychannels
		{
			use Helper;

			protected $baseurl;
			protected $currency;

			public function __construct($env = 'production')
				{
					$this->baseurl  = rtrim($this->configValue(
						$env === 'production' ? 'payments.channels.tigo.production_url' : 'payments.channels.tigo.sandbox_url',
						'https://secure.tigo.com'
					),                      '/');
					$this->currency = $this->configValue('payments.channels.tigo.currency', 'TZS');
				}

			public function RegisterURL($request)
				{
					return $this->api($request, $this->requestValue($request, 'endpoint', '/callbacks/register'), 'post', [
						'callbackUrl' => $this->requestValue($request, 'callback_url'),
						'service'     => $this->requestValue($request, 'service', 'payments'),
					]);
				}

			public function cert_encrypt($plaintext)
				{
					return base64_encode($plaintext);
				}

			public function balance($request)
				{
					return $this->authorized($request, 'get', $this->requestValue($request, 'endpoint', '/accounts/balance'));
				}

			public function stkpush($request)
				{
					return $this->authorized($request, 'post', $this->requestValue($request, 'endpoint', '/payments/authorize'), [
						'amount'      => $this->requestValue($request, 'amount'),
						'currency'    => $this->requestValue($request, 'currency', $this->currency),
						'msisdn'      => $this->requestValue($request, 'msisdn'),
						'reference'   => $this->requestValue($request, 'ref', $this->requestValue($request, 'reference')),
						'description' => $this->requestValue($request, 'desc', $this->requestValue($request, 'description')),
						'callbackUrl' => $this->requestValue($request, 'callback_url'),
					]);
				}

			public function b2c($request)
				{
					return $this->authorized($request, 'post', $this->requestValue($request, 'endpoint', '/disbursements'), [
						'amount'      => $this->requestValue($request, 'amount'),
						'currency'    => $this->requestValue($request, 'currency', $this->currency),
						'msisdn'      => $this->requestValue($request, 'msisdn'),
						'reference'   => $this->requestValue($request, 'ref', $this->requestValue($request, 'reference')),
						'description' => $this->requestValue($request, 'desc', $this->requestValue($request, 'description')),
					]);
				}

			public function b2b($request)
				{
					return $this->authorized($request, 'post', $this->requestValue($request, 'endpoint', '/transfers'), $this->requestData($request));
				}

			public function refund($request)
				{
					return $this->authorized($request, 'post', $this->requestValue($request, 'endpoint', '/payments/refund'), $this->requestData($request));
				}

			public function generate_token($request)
				{
					$grantType = $this->requestValue($request, 'grant_type', 'client_credentials');
					$payload   = [
						'grant_type'    => $grantType,
						'client_id'     => $this->requestValue($request, 'consumerkey', $this->requestValue($request, 'client_id')),
						'client_secret' => $this->requestValue($request, 'consumersecret', $this->requestValue($request, 'client_secret')),
					];

					if ($grantType === 'password')
						{
							$payload['username'] = $this->requestValue($request, 'username');
							$payload['password'] = $this->requestValue($request, 'password');
						}

					$response = Http::asForm()
					                ->withOptions(['verify' => $this->resourcePath('cacert.pem'), 'http_errors' => false])
					                ->post($this->url($this->requestValue($request, 'token_endpoint', '/oauth/token')), $payload);

					return $response->successful() ? $response->object() : null;
				}

			public function transactionStatus($request)
				{
					return $this->authorized($request, 'get', $this->requestValue($request, 'endpoint', '/transactions/' . $this->requestValue($request, 'transaction_id', $this->requestValue($request, 'id'))));
				}

			public function api($request, $endpoint = null, $method = 'post', ?array $payload = null)
				{
					return $this->authorized($request, $method, $endpoint ?: $this->requestValue($request, 'endpoint'), $payload ?: $this->requestData($request));
				}

			protected function authorized($request, $method, $endpoint, array $payload = [])
				{
					$token = $this->generate_token($request);

					return $this->jsonRequest($method, $this->url($endpoint), $payload, $token ? $token->access_token : null, [
						'X-Correlation-ID' => $this->requestValue($request, 'correlation_id', $this->uuid()),
					]);
				}

			protected function uuid()
				{
					$data    = random_bytes(16);
					$data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
					$data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

					return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
				}

			protected function url($endpoint)
				{
					return $this->baseurl . '/' . ltrim($endpoint, '/');
				}
		}
