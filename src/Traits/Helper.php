<?php
	
	namespace Caydeesoft\Payments\Traits;
	
	use Caydeesoft\Payments\Exceptions\PaymentException;
	use Illuminate\Support\Facades\Http;
	
	
	trait Helper
		{
			protected $provider = null;
			
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
					if (is_array($request))
						{
							return array_key_exists($key, $request) ? $request[$key] : $default;
						}
					
					return isset($request->{$key}) ? $request->{$key} : $default;
				}
			
			protected function credentialValue($provider, $request, $key, $default = null)
				{
					$value = $this->configValue("payments.channels.{$provider}.credentials.{$key}");
					
					if ($value !== null && $value !== '')
						{
							return $value;
						}
					
					return $this->requestValue($request, $key, $default);
				}
			
			protected function requestData($request)
				{
					if (is_array($request))
						{
							return $request;
						}
					
					if (method_exists($request, 'all'))
						{
							return $request->all();
						}
					
					return get_object_vars($request);
				}
			
			protected function jsonRequest($method, $url, array $payload = [], $token = null, array $headers = [])
				{
					$client = Http::withHeaders(array_merge(['Content-Type' => 'application/json'], $headers))
					              ->withOptions(['verify' => $this->caBundle(), 'http_errors' => false]);
					
					if ($token)
						{
							$client = $client->withToken($token);
						}
					
					$response = strtolower($method) === 'get'
						? $client->get($url, $payload)
						: $client->{strtolower($method)}($url, $payload);
					
					if ($response->successful())
						{
							return $response->object();
						}
					
					throw PaymentException::providerError($response->status(), $this->providerName(), $url, $this->redactString($response->body()));
				}
			
			public function invoke_server($link, $dt, $token, $method = 'post')
				{
					if ($method == 'post')
						$data = Http::withHeaders(['Content-Type:application/json'])
						            ->withOptions(['verify' => $this->caBundle(), 'http_errors' => false])
						            ->withToken($token)
						            ->post($link, $dt);
					else if ($method == 'put')
						$data = Http::withHeaders(['Content-Type:application/json'])
						            ->withOptions(['verify' => $this->caBundle(), 'http_errors' => false])
						            ->withToken($token)
						            ->put($link, $dt);
					else if ($method == 'get')
						$data = Http::withHeaders(['Content-Type:application/json'])
						            ->withOptions(['verify' => $this->caBundle(), 'http_errors' => false])
						            ->withToken($token)
						            ->get($link, $dt);
					
					if ($data->successful())
						{
							return $data->object();
						}
					throw PaymentException::providerError($data->status(), $this->providerName(), $link, $this->redactString($data->body()));
				}
			
			protected function caBundle()
				{
					$bundle = $this->configValue('payments.http.ca_bundle');
					
					if ($bundle === 'package')
						{
							return $this->resourcePath('cacert.pem');
						}
					
					return $bundle ?: true;
				}
			
			protected function assertAllowedEndpoint($provider, $endpoint, $strict = false)
				{
					$allowed = $this->configValue("payments.channels.{$provider}.allowed_endpoints", []);
					
					if (empty($allowed))
						{
							if ($strict)
								{
									throw new PaymentException(400, "Custom endpoint overrides for [{$provider}] require an allowed_endpoints config entry.");
								}
							
							return;
						}
					
					foreach ($allowed as $pattern)
						{
							if ($pattern === $endpoint || fnmatch($pattern, $endpoint))
								{
									return;
								}
						}
					
					throw new PaymentException(400, "Endpoint [{$endpoint}] is not allowed for [{$provider}].");
				}
			
			protected function redactHeaders(array $headers)
				{
					$redacted  = [];
					$sensitive = array_map('strtolower', $this->configValue('payments.callbacks.redact_headers', []));
					
					foreach ($headers as $key => $value)
						{
							$redacted[$key] = in_array(strtolower($key), $sensitive, true) ? ['[REDACTED]'] : $value;
						}
					
					return $redacted;
				}
			
			protected function redactArray(array $data)
				{
					$sensitive = ['consumerkey', 'consumersecret', 'client_secret', 'password', 'pin', 'credential', 'passkey', 'api_key', 'subscription_key'];
					
					foreach ($data as $key => $value)
						{
							if (in_array(strtolower((string)$key), $sensitive, true))
								{
									$data[$key] = '[REDACTED]';
									continue;
								}
							
							if (is_array($value))
								{
									$data[$key] = $this->redactArray($value);
								}
						}
					
					return $data;
				}
			
			protected function redactString($value)
				{
					return preg_replace('/(consumersecret|client_secret|password|pin|credential|passkey|api_key|subscription_key)["\']?\s*[:=]\s*["\']?([^"\',}\s]+)/i', '$1=[REDACTED]', (string)$value);
				}
			
			protected function providerName()
				{
					if ($this->provider)
						{
							return $this->provider;
						}
					
					$parts = explode('\\', static::class);
					
					return strtolower(end($parts));
				}
			
			public function msisdnFormatter(string $msisdn, $prefix = 254, int $size = 9)
			: string
				{
					return $prefix . substr($msisdn, -($size));
				}
		}
