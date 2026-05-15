<?php

	namespace Caydeesoft\Payments\Callbacks;

	use Caydeesoft\Payments\Traits\Helper;
	use Illuminate\Http\Request;

	class Mpesa implements CallbackInterface
		{
			use Helper;

			public function handleRequest($request, $isArray = true)
				{
					$rawData = $request->getContent();
					$decoded = json_decode($rawData, $isArray);

					if (json_last_error() === JSON_ERROR_NONE && $decoded !== null)
						{
							return $decoded;
						}

					return $isArray ? $request->all() : json_decode(json_encode($request->all()));
				}

			public function b2b(Request $request)
				{
					$callbackData = $this->handleRequest($request, true);
					$result       = $callbackData['Result'] ?? [];

					return $this->transactionResult($result);
				}

			public function b2c(Request $request)
				{
					$callbackData = $this->handleRequest($request, true);
					$result       = $callbackData['Result'] ?? [];

					return $this->transactionResult($result);
				}

			public function validation(Request $request)
				{
					$payload   = $this->c2bPayload($request);
					$validator = config('payments.callbacks.mpesa_validator');

					if ($validator && is_callable($validator))
						{
							$response = call_user_func($validator, $payload, $request);

							if ($response)
								{
									return array_merge($payload, ['response' => $response]);
								}
						}

					return array_merge($payload, [
						'response' => config('payments.callbacks.mpesa_validation_fallback', [
							'ResultCode' => '0',
							'ResultDesc' => 'Accepted',
						]),
					]);
				}

			public function confirmation(Request $request)
				{
					return array_merge($this->c2bPayload($request), [
						'response' => [
							'ResultCode' => 0,
							'ResultDesc' => 'Success',
						],
					]);
				}

			public function account_balance(Request $request)
				{
					$callbackData = $this->handleRequest($request, true);
					$result       = $callbackData['Result'] ?? [];
					$parameters   = $this->resultParameters($result);

					return array_merge($this->transactionResult($result), [
						'resultType'      => $result['ResultType'] ?? null,
						'accountBalance'  => $parameters['AccountBalance'] ?? $this->parameterByIndex($result, 0),
						'BOCompletedTime' => $parameters['BOCompletedTime'] ?? $this->parameterByIndex($result, 1),
						'parameters'      => $parameters,
					]);
				}

			public function reversal(Request $request)
				{
					$callbackData = $this->handleRequest($request, true);
					$result       = $callbackData['Result'] ?? [];

					return array_merge($this->transactionResult($result), [
						'resultType' => $result['ResultType'] ?? null,
						'receipt_no' => $result['TransactionID'] ?? null,
					]);
				}

			public function stk_push_request(Request $request)
				{
					$callbackData = $this->handleRequest($request, true);
					$callback     = $callbackData['Body']['stkCallback'] ?? [];
					$metadata     = collect($callback['CallbackMetadata']['Item'] ?? [])
						->mapWithKeys(function ($item)
							{
								return [$item['Name'] => $item['Value'] ?? null];
							})
						->toArray();

					return [
						'merchantRequestID'  => $callback['MerchantRequestID'] ?? null,
						'checkoutRequestID'  => $callback['CheckoutRequestID'] ?? null,
						'resultCode'         => $callback['ResultCode'] ?? null,
						'resultDesc'         => $callback['ResultDesc'] ?? null,
						'status'             => ($callback['ResultCode'] ?? null) == 0 ? 'success' : 'failed',
						'completed'          => 1,
						'amount'             => $metadata['Amount'] ?? null,
						'mpesaReceiptNumber' => $metadata['MpesaReceiptNumber'] ?? null,
						'transactionDate'    => $metadata['TransactionDate'] ?? null,
						'phoneNumber'        => $metadata['PhoneNumber'] ?? null,
						'metadata'           => $metadata,
						'callback_payload'   => $callback,
					];
				}

			public function stk_push_query(Request $request)
				{
					$callbackData = $this->handleRequest($request, true);

					return [
						'responseCode'        => $callbackData['ResponseCode'] ?? null,
						'responseDescription' => $callbackData['ResponseDescription'] ?? null,
						'merchantRequestID'   => $callbackData['MerchantRequestID'] ?? null,
						'checkoutRequestID'   => $callbackData['CheckoutRequestID'] ?? null,
						'resultCode'          => $callbackData['ResultCode'] ?? null,
						'resultDesc'          => $callbackData['ResultDesc'] ?? null,
						'callback_payload'    => $callbackData,
					];
				}

			public function transaction_status(Request $request)
				{
					$callbackData = $this->handleRequest($request, true);
					$result       = $callbackData['Result'] ?? [];
					$parameters   = $this->resultParameters($result);

					return array_merge($this->transactionResult($result), [
						'receiptNo'         => $parameters['ReceiptNo'] ?? $this->parameterByIndex($result, 0),
						'finalisedTime'     => $parameters['FinalisedTime'] ?? $this->parameterByIndex($result, 2),
						'amount'            => $parameters['Amount'] ?? $this->parameterByIndex($result, 3),
						'transactionStatus' => $parameters['TransactionStatus'] ?? $this->parameterByIndex($result, 4),
						'reasonType'        => $parameters['ReasonType'] ?? $this->parameterByIndex($result, 5),
						'transactionReason' => $parameters['TransactionReason'] ?? $this->parameterByIndex($result, 6),
						'debitPartyName'    => $parameters['DebitPartyName'] ?? $this->parameterByIndex($result, 12),
						'referenceData'     => $result['ReferenceData'] ?? null,
						'parameters'        => $parameters,
					]);
				}

			public function bill_manager_optin(Request $request)
				{
					return $this->genericPayload($request, 'bill-manager-optin');
				}

			public function bill_manager_invoice(Request $request)
				{
					return $this->genericPayload($request, 'bill-manager-invoice');
				}

			public function bill_manager_payment(Request $request)
				{
					return $this->genericPayload($request, 'bill-manager-payment');
				}

			public function ratiba(Request $request)
				{
					return $this->genericPayload($request, 'ratiba');
				}

			public function processB2BRequestCallback(Request $request)
				{
					return $this->b2b($request);
				}

			public function processB2CRequestCallback(Request $request)
				{
					return $this->b2c($request);
				}

			public function C2BRequestValidation(Request $request)
				{
					return $this->validation($request);
				}

			public function processC2BRequestConfirmation(Request $request)
				{
					return $this->confirmation($request);
				}

			public function processAccountBalanceRequestCallback(Request $request)
				{
					return $this->account_balance($request);
				}

			public function processReversalRequestCallBack(Request $request)
				{
					return $this->reversal($request);
				}

			public function processSTKPushRequestCallback(Request $request)
				{
					return $this->stk_push_request($request);
				}

			public function processSTKPushQueryRequestCallback(Request $request)
				{
					return $this->stk_push_query($request);
				}

			public function processTransactionStatusRequestCallback(Request $request)
				{
					return $this->transaction_status($request);
				}

			protected function c2bPayload(Request $request)
				{
					$callbackData = $this->handleRequest($request, true);

					return [
						'transactionType'   => $callbackData['TransactionType'] ?? null,
						'transID'           => $callbackData['TransID'] ?? null,
						'transTime'         => $callbackData['TransTime'] ?? null,
						'transAmount'       => $callbackData['TransAmount'] ?? null,
						'businessShortCode' => $callbackData['BusinessShortCode'] ?? null,
						'billRefNumber'     => $callbackData['BillRefNumber'] ?? null,
						'invoiceNumber'     => $callbackData['InvoiceNumber'] ?? null,
						'orgAccountBalance' => $callbackData['OrgAccountBalance'] ?? null,
						'thirdPartyTransID' => $callbackData['ThirdPartyTransID'] ?? null,
						'MSISDN'            => $callbackData['MSISDN'] ?? null,
						'firstName'         => $callbackData['FirstName'] ?? null,
						'middleName'        => $callbackData['MiddleName'] ?? null,
						'lastName'          => $callbackData['LastName'] ?? null,
						'callback_payload'  => $callbackData,
					];
				}

			protected function transactionResult(array $result)
				{
					$resultCode = $result['ResultCode'] ?? null;

					return [
						'originatorConversationID' => $result['OriginatorConversationID'] ?? null,
						'conversationID'           => $result['ConversationID'] ?? null,
						'transactionID'            => $result['TransactionID'] ?? null,
						'resultCode'               => $resultCode,
						'resultDesc'               => $result['ResultDesc'] ?? null,
						'status'                   => $resultCode == 0 ? 'success' : 'failed',
						'completed'                => $resultCode == 0 ? 1 : 0,
						'callback_payload'         => $result,
					];
				}

			protected function resultParameters(array $result)
				{
					return collect($result['ResultParameters']['ResultParameter'] ?? [])
						->mapWithKeys(function ($item)
							{
								$key = $item['Key'] ?? $item['Name'] ?? null;

								return $key ? [$key => $item['Value'] ?? null] : [];
							})
						->toArray();
				}

			protected function parameterByIndex(array $result, $index)
				{
					return $result['ResultParameters']['ResultParameter'][$index]['Value'] ?? null;
				}

			protected function genericPayload(Request $request, $event)
				{
					return [
						'event'   => $event,
						'headers' => $this->redactHeaders($request->headers->all()),
						'payload' => $this->redactArray($this->handleRequest($request, true)),
						'raw'     => $request->getContent(),
					];
				}
		}
