<?php

	namespace Caydeesoft\Payments\Libs;

	use Caydeesoft\Payments\Exceptions\PaymentException;

	class Payments
		{
			protected $channel;

			protected $environment;

			public function __construct($channel = 'mpesa', $environment = 'sandbox')
				{
					$this->environment = $environment;
					$this->channel     = $channel instanceof Paychannels
						? $channel
						: $this->makeChannel($channel);
				}

			public function channel($channel, $environment = null)
				{
					return new static($channel, $environment ?: $this->environment);
				}

			protected function makeChannel($channel)
				{
					$drivers = [
						'mpesa'       => Mpesa::class,
						'airtel'      => AirtelMoney::class,
						'airtelmoney' => AirtelMoney::class,
						'tkash'       => Tkash::class,
						'eazzy'       => EazzyPay::class,
						'eazzypay'    => EazzyPay::class,
						'mtn'         => Mtn::class,
						'tigo'        => Tigo::class,
					];

					$key = strtolower((string)$channel);

					if (!isset($drivers[$key]))
						{
							throw new PaymentException(400, "Unsupported payment channel [{$channel}].");
						}

					return new $drivers[$key]($this->environment);
				}

		/**
		 * @param $request
		 * @return void
		 */
			public function generate_token($request)
				{
					return $this->channel->generate_token($request);
				}

		/**
		 * @param $request
		 * @return mixed
		 */
			public function stkpush($request)
				{
					return $this->channel->stkpush($request);
				}

		/**
		 * @param $request
		 * @return void
		 */
			public function registerURL($request)
				{
					return $this->channel->RegisterURL($request);
				}

		/**
		 * @param $request
		 * @return mixed
		 */
			public function balance($request)
				{
					return $this->channel->balance($request);
				}

			public function refund($request)
				{
					return $this->channel->refund($request);
				}

			public function b2c($request)
				{
					return $this->channel->b2c($request);
				}

			public function b2b($request)
				{
					return $this->channel->b2b($request);
				}

			public function __call($method, $arguments)
				{
					if (!method_exists($this->channel, $method))
						{
							throw new PaymentException(400, "Unsupported payment operation [{$method}].");
						}

					return $this->channel->{$method}(...$arguments);
				}
		}
