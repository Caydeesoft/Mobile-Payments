<?php

	namespace Caydeesoft\Payments\Libs;

	use Caydeesoft\Payments\Constants\MpesaParameters;
	use Caydeesoft\Payments\Traits\Helper;
	use Illuminate\Support\Facades\Http;
	use Illuminate\Support\Facades\Log;
	use Symfony\Component\HttpKernel\Exception\HttpException;

	class Mpesa extends MpesaParameters implements Paychannels
	{
		use Helper;
		protected $provider = 'mpesa';

			public $link, $cert, $timestamp;

			public function __construct($env = 'production')
				{
					$this->timestamp = date('YmdHis');

					if ($env == 'production')
						{
							$this->link = rtrim($this->configValue('payments.channels.mpesa.production_url', 'https://api.safaricom.co.ke'), '/');
							$this->cert = $this->resourcePath('Mpesa_public_cert.cer');
						}
					else
						{
							$this->link = rtrim($this->configValue('payments.channels.mpesa.sandbox_url', 'https://sandbox.safaricom.co.ke'), '/');
							$this->cert = $this->resourcePath('Mpesa_public_sandbox_cert.cer');
						}
				}

			public function generate_token($request)
				{
					try
						{
							$credentials = base64_encode($this->credentialValue('mpesa', $request, 'consumerkey') . ':' . $this->credentialValue('mpesa', $request, 'consumersecret'));
							$data        = Http::withHeaders(['Content-Type' => 'application/json', 'Authorization' => 'Basic ' . $credentials])
							                   ->withOptions(['verify' => $this->caBundle(), 'http_errors' => false])
							                   ->get($this->link . self::token_link);

							if ($data->successful())
								{
									return $data->object();
								}

						}
					catch (HttpException $e)
						{
							Log::error($e->getMessage());
						}
				}

			public function api($request, $endpoint = null, $method = 'post', ?array $payload = null)
					{
						$path = $endpoint ?: $this->requestValue($request, 'endpoint');
						$this->assertAllowedEndpoint('mpesa', $path, true);
						$data = $payload ?: $this->requestValue($request, 'payload', $this->requestData($request));

					return $this->invoke_server($this->link . $path, $data, $this->accessToken($request), $method);
				}

			protected function accessToken($request)
				{
					$token = $this->generate_token($request);

					return isset($token->access_token) ? $token->access_token : null;
				}

			protected function endpoint($key, $fallback)
				{
					return $this->configValue('payments.channels.mpesa.endpoints.' . $key, $fallback);
				}

		/**
		 * @param $plaintext
		 * @return string
		 */
			public function cert_encrypt($plaintext)
				{
					$cert      = $this->cert;
					$fp        = fopen($cert, "r");
					$publicKey = fread($fp, filesize($cert));
					fclose($fp);
					openssl_get_publickey($publicKey);
					openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
					return base64_encode($encrypted);
				}

		/**
		 * @param $type
		 * @return int
		 */
			public function getIdentifier($type)
				{
					$type = strtolower($type);
					switch ($type)
						{
						case "msisdn":
								$x = 1;
								break;
						case "tillnumber":
								$x = 2;
								break;
						case "shortcode":
								$x = 4;
								break;
						default:
								$x = 4;
						}
					return $x;
				}

		/**
		 * @param $request
		 * @return array|object|void
		 */
			public function stkpush($request)
				{
					try
						{
							//dd($this->generate_token($request)->access_token);
							$businessShortcode = $this->credentialValue('mpesa', $request, 'shortcode', $request->shortcode);
							$passkey           = $this->credentialValue('mpesa', $request, 'passkey', $request->passkey);
							$password          = base64_encode($businessShortcode . $passkey . $this->timestamp);
							$type      = ($request->type == 'TILL') ? 'CustomerBuyGoodsOnline' : 'CustomerPayBillOnline';
							$shortcode = ($request->type == 'TILL') ? $request->cshortcode : $businessShortcode;
							$data      = [
								'BusinessShortCode' => $businessShortcode,
								'Password'          => $password,
								'Timestamp'         => $this->timestamp,
								'TransactionType'   => $type,
								'Amount'            => $request->amount,
								'PartyA'            => $request->msisdn,
								'PartyB'            => $shortcode,
								'PhoneNumber'       => $request->msisdn,
								'CallBackURL'       => self::stkrequestcallback(),
								'AccountReference'  => $request->ref,
								'TransactionDesc'   => $request->desc
							];
							$token     = $this->generate_token($request);
							if (property_exists($token, 'access_token'))
								{
									return $this->invoke_server($this->link . self::checkout_processlink, $data, $token->access_token);
								}

						}
					catch (HttpException $e)
						{
							Log::error($e->getMessage());
						}
				}

		/**
		 * @param $request
		 *
		 * @return object
		 */
			public function checkout_query($request)
				{
					try
						{
							$businessShortcode = $this->credentialValue('mpesa', $request, 'shortcode', $request->shortcode);
							$passkey           = $this->credentialValue('mpesa', $request, 'passkey', $request->passkey);
							$password          = base64_encode($businessShortcode . $passkey . $this->timestamp);
							$data     = [
								'BusinessShortCode' => $businessShortcode,
								'Password'          => $password,
								'Timestamp'         => $this->timestamp,
								'CheckoutRequestID' => $request->CheckoutRequestID
							];
							return $this->invoke_server($this->link . self::checkout_querylink, $data, $this->accessToken($request));
						}
					catch (HttpException $e)
						{
							Log::error($e->getMessage());
						}

				}

		/**
		 * @param $request
		 * @return array|object|void
		 */
			public function refund($request)
				{
					try
						{
							$data = [
								'Initiator'              => $request->initiator,
								'SecurityCredential'     => $this->cert_encrypt($request->credential),
								'CommandID'              => 'TransactionReversal',
								'TransactionID'          => $request->TransID,
								'Amount'                 => $request->amount,
								'ReceiverParty'          => $request->receiver,
								'RecieverIdentifierType' => $this->getIdentifier($request->receiverType),
								'ResultURL'              => self::reversalURL(),
								'QueueTimeOutURL'        => self::reversalURL(),
								'Remarks'                => $request->remarks,
								'Occasion'               => $request->ocassion
							];
							return $this->invoke_server($this->link . self::reversal_link, $data, $this->generate_token($request)->access_token);
						}
					catch (HttpException $e)
						{
							Log::error($e->getMessage());
						}
				}

		/**
		 * @param $request
		 * @return array|object|void
		 */
			public function balance($request)
				{
					try
						{
							$data = [
								'Initiator'          => $request->initiator,
								'SecurityCredential' => $this->cert_encrypt($request->credential),
								'CommandID'          => 'AccountBalance',
								'PartyA'             => $request->shortcode,
								'IdentifierType'     => $this->getIdentifier($request->type),
								'Remarks'            => $request->remark,
								'QueueTimeOutURL'    => self::accountbalcallback(),
								'ResultURL'          => self::accountbalcallback()
							];

							return $this->invoke_server($this->link . self::balance_link, $data, $this->generate_token($request)->access_token);
						}
					catch (HttpException $e)
						{
							Log::error($e->getMessage());
						}
				}

		/**
		 * @param $request
		 * @return array|object|void
		 */
			public function RegisterURL($request)
				{
					try
						{
							$data = [
								'ValidationURL'   => self::c2bvalidationcallback(),
								'ConfirmationURL' => self::c2bconfirmationcallback(),
								'ResponseType '   => 'Canceled',
								'ShortCode'       => $request->shortcode
							];

							return $this->invoke_server($this->link . self::c2b_regiterUrl, $data, $this->generate_token($request)->access_token);
						}
					catch (HttpException $e)
						{
							Log::error($e->getMessage());
						}
				}

		/**
		 * @param $request
		 * @return array|object|void
		 */
			public function b2b($request)
				{
					try
						{
							$data = [
								'Initiator'              => $request->initiator,
								'SecurityCredential'     => $this->cert_encrypt($request->credential),
								'CommandID'              => $request->CommandID,
								'SenderIdentifierType'   => $this->getIdentifier($request->sender_type),
								'RecieverIdentifierType' => $this->getIdentifier($request->receiver_type),
								'Amount'                 => $request->amount,
								'PartyA'                 => $request->from,
								'PartyB'                 => $request->to,
								'AccountReference'       => $request->accountref,
								'Remarks'                => $request->remarks,
								'QueueTimeOutURL'        => self::b2bcallback(),
								'ResultURL'              => self::b2bcallback()
							];
							return $this->invoke_server($this->link . self::b2b_link, $data, $this->generate_token($request)->access_token);
						}
					catch (HttpException $e)
						{
							Log::error($e->getMessage());
						}

				}

		/**
		 * @param $request
		 * @return array|object|void
		 */
			public function b2c($request)
				{
					try
						{
							$data = [
								'InitiatorName'      => $request->initiator,
								'SecurityCredential' => $this->cert_encrypt($request->credential),
								'CommandID'          => $request->CommandID,
								'Amount'             => $request->amount,
								'PartyA'             => $request->shortcode,
								'PartyB'             => $request->msisdn,
								'Remarks'            => $request->remarks,
								'QueueTimeOutURL'    => self::b2ccallback(),
								'ResultURL'          => self::b2ccallback(),
								'Occasion'           => $request->ocassion
							];
							return $this->invoke_server($this->link . self::b2c_link, $data, $this->generate_token($request)->access_token);
						}
					catch (HttpException $e)
						{
							Log::error($e->getMessage());
						}

				}

		/**
		 * @param $request
		 * @return array|object|void
		 */
			public function transactionstatus($request)
				{
					try
						{
							$data = [
								'Initiator'              => $request->initiator,
								'SecurityCredential'     => $this->cert_encrypt($request->credential),
								'CommandID'              => 'TransactionStatusQuery',
								'TransactionID'          => $request->transID,
								'PartyA'                 => $request->msisdn,
								'IdentifierType'         => $this->getIdentifier($request->identifier),
								'ResultURL'              => self::transtatURL(),
								'QueueTimeOutURL'        => self::transtatURL(),
								'Remarks'                => $request->remarks,
								'Occasion'               => $request->ocassion,
								'OriginalConversationID' => $request->conversionID
							];


							return $this->invoke_server($this->link . self::transtat_link, $data, $this->generate_token($request)->access_token);
						}
					catch (HttpException $e)
						{
							Log::error($e->getMessage());
						}

				}

		/**
		 * @param $request
		 * @return array|object|void
		 */
			public function qr($request)
				{
					try
						{
							$data = [
								"QRVersion"    => $request->qrversion,
								"TrxCode"      => $request->trxcode,//BG,WA,PB,SM,SB
								"CPI"          => $request->cpi,
								"MerchantName" => $request->merchantname,
								"Amount"       => $request->amount,
								"RefNo"        => $request->refno,
								"QRFormat"     => $request->qrformat, //1: image, 2: QR String, 3: Binary, 4: PDF
								"QRType"       => $request->qrtype // S : Static, D : Dynamic
							];


							return $this->invoke_server($this->link . self::qrcode, $data, $this->generate_token($request)->access_token);
						}
					catch (HttpException $e)
						{
							Log::error($e->getMessage());
						}
				}

			public function c2bSimulate($request)
				{
					$data = [
						'ShortCode'     => $this->requestValue($request, 'shortcode'),
						'CommandID'     => $this->requestValue($request, 'CommandID', $this->requestValue($request, 'command', 'CustomerPayBillOnline')),
						'Amount'        => $this->requestValue($request, 'amount'),
						'Msisdn'        => $this->requestValue($request, 'msisdn'),
						'BillRefNumber' => $this->requestValue($request, 'ref', $this->requestValue($request, 'BillRefNumber')),
					];

					return $this->invoke_server($this->link . self::c2b_simulate, $data, $this->accessToken($request));
				}

			public function taxRemittance($request)
				{
					$data = [
						'Initiator'              => $this->requestValue($request, 'initiator'),
						'SecurityCredential'     => $this->cert_encrypt($this->requestValue($request, 'credential')),
						'CommandID'              => $this->requestValue($request, 'CommandID', 'PayTaxToKRA'),
						'SenderIdentifierType'   => $this->getIdentifier($this->requestValue($request, 'sender_type', 'shortcode')),
						'RecieverIdentifierType' => $this->getIdentifier($this->requestValue($request, 'receiver_type', 'shortcode')),
						'Amount'                 => $this->requestValue($request, 'amount'),
						'PartyA'                 => $this->requestValue($request, 'from', $this->requestValue($request, 'shortcode')),
						'PartyB'                 => $this->requestValue($request, 'to', $this->requestValue($request, 'kra_shortcode')),
						'AccountReference'       => $this->requestValue($request, 'accountref', $this->requestValue($request, 'ref')),
						'Remarks'                => $this->requestValue($request, 'remarks'),
						'QueueTimeOutURL'        => self::b2bcallback(),
						'ResultURL'              => self::b2bcallback(),
					];

					return $this->invoke_server($this->link . self::tax_remittance, $data, $this->accessToken($request));
				}

			public function ratibaCreate($request)
				{
					return $this->ratiba($request, 'ratiba_create', self::ratiba_create);
				}

			public function ratibaUpdate($request)
				{
					return $this->ratiba($request, 'ratiba_update', self::ratiba_update);
				}

			public function ratibaCancel($request)
				{
					return $this->ratiba($request, 'ratiba_cancel', self::ratiba_cancel);
				}

			public function ratibaQuery($request)
				{
					return $this->ratiba($request, 'ratiba_query', self::ratiba_query);
				}

			public function ratibaCallback($request)
				{
					return $this->ratiba($request, 'ratiba_callback', self::ratiba_callback);
				}

			protected function ratiba($request, $endpointKey, $fallbackEndpoint)
				{
					$payload = $this->requestValue($request, 'payload', $this->requestData($request));

					if (!isset($payload['CallBackURL']) && !isset($payload['CallbackURL']) && !isset($payload['callbackUrl']))
						{
							$payload['CallBackURL'] = self::ratibaURL();
						}

					return $this->invoke_server($this->link . $this->endpoint($endpointKey, $fallbackEndpoint), $payload, $this->accessToken($request));
				}

			public function billManagerOptin($request, $state = 0)
				{
					try
						{
							$data = [
								"shortcode"       => $request->shortcode,
								"logo"            => $request->logo,
								"email"           => $request->email,
								"officialContact" => $request->msisdn,
								"sendReminders"   => 1,
								"callbackUrl"     => self::billManagerOptinURL()
							];
							$link = ($state == 0) ? self::billMOptinLink : self::billMChangeOptinLink;

							return $this->invoke_server($this->link . $link, $data, $this->generate_token($request)->access_token);
						}
					catch (HttpException $e)
						{
							Log::error($e->getMessage());
						}
				}

			public function billManagerChangeOptin($request)
				{
					return $this->billManagerOptin($request, 1);
				}

			public function billManagerSingleInvoice($request)
				{
					try
						{
							$data = [
								"externalReference" => $request->invoice_no,
								"billedFullName"    => $request->name,
								"billedPhoneNumber" => $request->msisdn,
								"billedPeriod"      => $request->billingPeriod,
								"invoiceName"       => $request->invoice_name,
								"dueDate"           => $request->due_date,
								"accountReference"  => $request->ref,
								"amount"            => $request->amount,
								"invoiceItems"      => ["itemName" => $request->item_name, "amount" => $request->item_amount]
							];


							return $this->invoke_server($this->link . self::billMSingleInvoice, $data, $this->generate_token($request)->access_token);
						}
					catch (HttpException $e)
						{
							Log::error($e->getMessage());
						}
				}

			public function billManagerBulkInvoice($request)
				{
					$data = $this->requestValue($request, 'payload', [
						'invoices'    => $this->requestValue($request, 'invoices', []),
						'shortcode'   => $this->requestValue($request, 'shortcode'),
						'callbackUrl' => $this->requestValue($request, 'callbackUrl', self::billManagerInvoiceURL()),
					]);

					return $this->invoke_server($this->link . self::billMBulkInvoice, $data, $this->accessToken($request));
				}

			public function billManagerCancelSingleInvoice($request, $data)
				{
					try
						{

							return $this->invoke_server($this->link . self::billMCancelSingleIn, $data, $this->generate_token($request)->access_token);
						}
					catch (HttpException $e)
						{
							Log::error($e->getMessage());
						}
				}

			public function billManagerCancelBulkInvoice($request, $data = null)
				{
					$payload = $data ?: $this->requestValue($request, 'payload', $this->requestData($request));

					return $this->invoke_server($this->link . self::billMCancelBulkIn, $payload, $this->accessToken($request));
				}

			public function billManagerInvoiceQuery($request)
				{
					$payload = $this->requestValue($request, 'payload', $this->requestData($request));

					return $this->invoke_server($this->link . self::billMInvoiceQuery, $payload, $this->accessToken($request));
				}

			public function billManagerPaymentQuery($request)
				{
					$payload = $this->requestValue($request, 'payload', $this->requestData($request));

					return $this->invoke_server($this->link . self::billMPaymentQuery, $payload, $this->accessToken($request));
				}

			public function billManagerReconciliation($request)
				{
					$payload = $this->requestValue($request, 'payload', $this->requestData($request));

					return $this->invoke_server($this->link . self::billMReconciliation, $payload, $this->accessToken($request));
				}
		}
