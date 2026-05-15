<?php

	namespace Caydeesoft\Payments\Libs;

	use Caydeesoft\Payments\Traits\Helper;
	use Illuminate\Support\Facades\Http;

	class Mtn implements Paychannels
		{
			use Helper;

			protected $baseurl;
			protected $targetEnvironment;
			protected $currency;

			public function __construct($env = 'production')
				{
					$this->baseurl           = rtrim($this->configValue(
						$env === 'production' ? 'payments.channels.mtn.production_url' : 'payments.channels.mtn.sandbox_url',
						$env === 'production' ? 'https://momodeveloper.mtn.com' : 'https://sandbox.momodeveloper.mtn.com'
					),                               '/');
					$this->targetEnvironment = $this->configValue('payments.channels.mtn.environment', $env === 'production' ? 'production' : 'sandbox');
					$this->currency          = $this->configValue('payments.channels.mtn.currency', 'EUR');
				}

			public function RegisterURL($request)
				{
					return [
						'message'      => 'MTN MoMo callbacks are usually configured in the partner portal or sent as callback URLs in API user setup.',
						'callback_url' => $this->requestValue($request, 'callback_url'),
					];
				}

			public function cert_encrypt($plaintext)
				{
					return base64_encode($plaintext);
				}

			public function balance($request)
				{
					$product = $this->requestValue($request, 'product', 'collection');

					return $this->authorized($request, $product, 'get', $this->requestValue($request, 'endpoint', "/{$product}/v1_0/account/balance"));
				}

			public function stkpush($request)
				{
					return $this->requestToPay($request);
				}

			public function b2c($request)
				{
					return $this->transfer($request);
				}

			public function b2b($request)
				{
					return $this->authorized($request, $this->requestValue($request, 'product', 'disbursement'), 'post', $this->requestValue($request, 'endpoint', '/disbursement/v1_0/transfer'), $this->requestData($request));
				}

			public function refund($request)
				{
					return $this->authorized($request, 'collection', 'post', $this->requestValue($request, 'endpoint', '/collection/v1_0/refund'), $this->requestData($request), [
						'X-Reference-Id' => $this->referenceId($request),
					]);
				}

			public function generate_token($request)
				{
					$product = $this->requestValue($request, 'product', 'collection');
					$user    = $this->requestValue($request, 'api_user', $this->requestValue($request, 'consumerkey'));
					$key     = $this->requestValue($request, 'api_key', $this->requestValue($request, 'consumersecret'));

					$response = Http::withBasicAuth($user, $key)
					                ->withHeaders([
						                              'Ocp-Apim-Subscription-Key' => $this->subscriptionKey($request, $product),
					                              ])
					                ->withOptions(['verify' => $this->resourcePath('cacert.pem'), 'http_errors' => false])
					                ->post($this->url("/{$product}/token/"));

					return $response->successful() ? $response->object() : null;
				}

			public function requestToPay($request)
				{
					return $this->authorized($request, 'collection', 'post', $this->requestValue($request, 'endpoint', '/collection/v1_0/requesttopay'), [
						'amount'       => (string)$this->requestValue($request, 'amount'),
						'currency'     => $this->requestValue($request, 'currency', $this->currency),
						'externalId'   => $this->requestValue($request, 'externalId', $this->requestValue($request, 'ref')),
						'payer'        => [
							'partyIdType' => $this->requestValue($request, 'partyIdType', 'MSISDN'),
							'partyId'     => $this->requestValue($request, 'msisdn'),
						],
						'payerMessage' => $this->requestValue($request, 'payerMessage', $this->requestValue($request, 'desc')),
						'payeeNote'    => $this->requestValue($request, 'payeeNote', $this->requestValue($request, 'remarks')),
					],                       [
						                         'X-Reference-Id' => $this->referenceId($request),
					                         ]);
				}

			public function requestToPayStatus($request)
				{
					$referenceId = $this->referenceId($request);

					return $this->authorized($request, 'collection', 'get', $this->requestValue($request, 'endpoint', "/collection/v1_0/requesttopay/{$referenceId}"));
				}

			public function transfer($request)
				{
					return $this->authorized($request, 'disbursement', 'post', $this->requestValue($request, 'endpoint', '/disbursement/v1_0/transfer'), [
						'amount'       => (string)$this->requestValue($request, 'amount'),
						'currency'     => $this->requestValue($request, 'currency', $this->currency),
						'externalId'   => $this->requestValue($request, 'externalId', $this->requestValue($request, 'ref')),
						'payee'        => [
							'partyIdType' => $this->requestValue($request, 'partyIdType', 'MSISDN'),
							'partyId'     => $this->requestValue($request, 'msisdn'),
						],
						'payerMessage' => $this->requestValue($request, 'payerMessage', $this->requestValue($request, 'desc')),
						'payeeNote'    => $this->requestValue($request, 'payeeNote', $this->requestValue($request, 'remarks')),
					],                       [
						                         'X-Reference-Id' => $this->referenceId($request),
					                         ]);
				}

			public function transferStatus($request)
				{
					$referenceId = $this->referenceId($request);

					return $this->authorized($request, 'disbursement', 'get', $this->requestValue($request, 'endpoint', "/disbursement/v1_0/transfer/{$referenceId}"));
				}

			public function api($request, $endpoint = null, $method = 'post', ?array $payload = null)
				{
					$product = $this->requestValue($request, 'product', 'collection');

					return $this->authorized($request, $product, $method, $endpoint ?: $this->requestValue($request, 'endpoint'), $payload ?: $this->requestData($request));
				}

			protected function authorized($request, $product, $method, $endpoint, array $payload = [], array $headers = [])
				{
					$token = $this->generate_token($this->withProduct($request, $product));

					return $this->jsonRequest($method, $this->url($endpoint), $payload, $token ? $token->access_token : null, array_merge([
						                                                                                                                      'X-Target-Environment' => $this->requestValue($request, 'target_environment', $this->targetEnvironment),
						                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      'Ocp-Apim-Subscription-Key' => $this->subscriptionKey($request, $product),
					                                                                                                                      ], $headers));
				}

			protected function subscriptionKey($request, $product)
				{
					return $this->requestValue($request, $product . '_subscription_key', $this->requestValue($request, 'subscription_key'));
				}

			protected function referenceId($request)
				{
					return $this->requestValue($request, 'reference_id', $this->requestValue($request, 'referenceId', $this->uuid()));
				}

			protected function withProduct($request, $product)
				{
					$data            = $this->requestData($request);
					$data['product'] = $product;

					return (object)$data;
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
