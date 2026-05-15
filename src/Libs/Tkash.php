<?php

	namespace Caydeesoft\Payments\Libs;

	use Caydeesoft\Payments\Constants\TkashParameters;
	use Caydeesoft\Payments\Traits\Helper;

	class Tkash implements Paychannels
		{
			use Helper;
			protected $provider = 'tkash';

			protected $link;

			public function __construct($env = 'production')
				{
					$this->link = rtrim($this->configValue(
						$env === 'production' ? 'payments.channels.tkash.production_url' : 'payments.channels.tkash.sandbox_url',
						$env === 'production' ? 'https://production.gw.mfs-tkl.com' : 'https://staging.gw.mfs-tkl.com'
					),                  '/');
				}

			public function cert_encrypt($plaintext)
				{
					return base64_encode($plaintext);
				}

			public function stkpush($request)
				{
					return $this->authorized($request, 'post', $this->requestValue($request, 'endpoint', TkashParameters::stkpush), $this->requestData($request));
				}

			public function generate_token($request)
				{
					$credentials = base64_encode($this->credentialValue('tkash', $request, 'consumerkey') . ':' . $this->credentialValue('tkash', $request, 'consumersecret'));

					return $this->jsonRequest('post', $this->url(TkashParameters::token_link), [
						'username' => $this->credentialValue('tkash', $request, 'username', $this->requestValue($request, 'name')),
						'password' => $this->credentialValue('tkash', $request, 'password'),
					],                        null, [
						                          'Authorization' => 'Basic ' . $credentials,
					                          ]);
				}

			public function RegisterURL($request)
				{
					return $this->authorized($request, 'post', TkashParameters::registerurl, [
						'registerUrlRequest' => [
							'consumerId'          => $this->requestValue($request, 'consumer_id'),
							'notificationUrl'     => TkashParameters::c2bconfirmationcallback(),
							'notificationUrlType' => 'REST',
							'validationUrl'       => TkashParameters::c2bvalidationcallback(),
							'validationUrlType'   => 'REST',
							'creationDate'        => date('d-M-y\TH:i:s'),
						],
					]);
				}

			public function UpdateURL($request)
				{
					return $this->authorized($request, 'put', TkashParameters::updateURL, [
						'registerUrlRequest' => [
							'consumerId'          => $this->requestValue($request, 'consumer_id'),
							'notificationUrl'     => TkashParameters::c2bconfirmationcallback(),
							'notificationUrlType' => 'REST',
							'validationUrl'       => TkashParameters::c2bvalidationcallback(),
							'validationUrlType'   => 'REST',
							'creationDate'        => date('d-M-y\TH:i:s'),
						],
					]);
				}

			public function replayNotification($request)
				{
					return $this->authorized($request, 'get', TkashParameters::replayNotification, [
						'notificationType' => $this->requestValue($request, 'notificationType'),
						'id'               => $this->requestValue($request, 'consumer_id'),
						'limit'            => $this->requestValue($request, 'limit'),
					]);
				}

			public function balance($request)
				{
					return $this->authorized($request, 'post', $this->requestValue($request, 'endpoint', TkashParameters::balance), $this->requestData($request));
				}

			public function b2c($request)
				{
					return $this->authorized($request, 'post', $this->requestValue($request, 'endpoint', TkashParameters::b2c), $this->requestData($request));
				}

			public function b2b($request)
				{
					return $this->authorized($request, 'post', $this->requestValue($request, 'endpoint', TkashParameters::b2b), $this->requestData($request));
				}

			public function refund($request)
				{
					return $this->authorized($request, 'post', $this->requestValue($request, 'endpoint', TkashParameters::refund), $this->requestData($request));
				}

			protected function authorized($request, $method, $endpoint, array $payload = [])
				{
					$this->assertAllowedEndpoint('tkash', $endpoint);
					$token = $this->generate_token($request);

					return $this->jsonRequest($method, $this->url($endpoint), $payload, isset($token->access_token) ? $token->access_token : null);
				}

			protected function url($endpoint)
				{
					return $this->link . '/' . ltrim($endpoint, '/');
				}
		}
