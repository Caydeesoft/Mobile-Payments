<?php

	namespace Caydeesoft\Payments\Http\Controllers;

	use Caydeesoft\Payments\Callbacks\Airtel;
	use Caydeesoft\Payments\Callbacks\CallbackInterface;
	use Caydeesoft\Payments\Callbacks\EazzyPay;
	use Caydeesoft\Payments\Callbacks\Mpesa;
	use Caydeesoft\Payments\Callbacks\Mtn;
	use Caydeesoft\Payments\Callbacks\Tigo;
	use Caydeesoft\Payments\Callbacks\Tkash;
	use Caydeesoft\Payments\Exceptions\PaymentException;
	use Illuminate\Http\Request;
	use Illuminate\Routing\Controller;

	class CallbackController extends Controller
		{
			protected $providers
				= [
					'airtel'      => Airtel::class,
					'airtelmoney' => Airtel::class,
					'eazzy'       => EazzyPay::class,
					'eazzypay'    => EazzyPay::class,
					'mpesa'       => Mpesa::class,
					'mtn'         => Mtn::class,
					'mtnmomo'     => Mtn::class,
					'tigo'        => Tigo::class,
					'tigopesa'    => Tigo::class,
					'tkash'       => Tkash::class,
				];

			protected $events
				= [
					'b2b'                  => 'processB2BRequestCallback',
					'b2c'                  => 'processB2CRequestCallback',
					'transaction-status'   => 'processTransactionStatusRequestCallback',
					'transactionstatus'    => 'processTransactionStatusRequestCallback',
					'stk-query'            => 'processSTKPushQueryRequestCallback',
					'stkpush-query'        => 'processSTKPushQueryRequestCallback',
					'stk'                  => 'processSTKPushRequestCallback',
					'stkpush'              => 'processSTKPushRequestCallback',
					'reversal'             => 'processReversalRequestCallBack',
					'balance'              => 'processAccountBalanceRequestCallback',
					'account-balance'      => 'processAccountBalanceRequestCallback',
					'c2b-confirmation'     => 'processC2BRequestConfirmation',
					'confirmation'         => 'processC2BRequestConfirmation',
					'c2b-validation'       => 'C2BRequestValidation',
					'validation'           => 'C2BRequestValidation',
					'bill-manager-optin'   => 'bill_manager_optin',
					'bill-manager-invoice' => 'bill_manager_invoice',
					'bill-manager-payment' => 'bill_manager_payment',
					'ratiba'               => 'ratiba',
				];

			public function handle(Request $request, $provider, $event)
				{
					$callback = $this->provider($provider);
					$method   = $this->event($event);
					$data     = $callback->{$method}($request);

					if (isset($data['response']) && strtolower($provider) === 'mpesa')
						{
							return response()->json($data['response']);
						}

					return response()->json([
						                        'accepted' => true,
						                        'provider' => strtolower($provider),
						                        'event'    => strtolower($event),
						                        'data'     => $data,
						                        'response' => config('payments.callbacks.success_response', ['ResultCode' => 0, 'ResultDesc' => 'Accepted']),
					                        ]);
				}

			public function mpesaB2B(Request $request)
				{
					return $this->handle($request, 'mpesa', 'b2b');
				}

			public function mpesaB2C(Request $request)
				{
					return $this->handle($request, 'mpesa', 'b2c');
				}

			public function mpesaTransactionStatus(Request $request)
				{
					return $this->handle($request, 'mpesa', 'transaction-status');
				}

			public function mpesaStk(Request $request)
				{
					return $this->handle($request, 'mpesa', 'stk');
				}

			public function mpesaReversal(Request $request)
				{
					return $this->handle($request, 'mpesa', 'reversal');
				}

			public function mpesaAccountBalance(Request $request)
				{
					return $this->handle($request, 'mpesa', 'account-balance');
				}

			public function mpesaC2BConfirmation(Request $request)
				{
					return $this->handle($request, 'mpesa', 'c2b-confirmation');
				}

			public function mpesaC2BValidation(Request $request)
				{
					return $this->handle($request, 'mpesa', 'c2b-validation');
				}

			protected function provider($provider)
				{
					$key = strtolower($provider);

					if (!isset($this->providers[$key]))
						{
							throw new PaymentException(400, "Unsupported callback provider [{$provider}].");
						}

					return app($this->providers[$key]);
				}

			protected function event($event)
				{
					$key = strtolower($event);

					if (!isset($this->events[$key]))
						{
							throw new PaymentException(400, "Unsupported callback event [{$event}].");
						}

					return $this->events[$key];
				}
		}
