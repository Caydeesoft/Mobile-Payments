<?php

namespace Caydeesoft\Payments\Callbacks;

use Illuminate\Http\Request;

class Mpesa implements CallbackInterface
    {
        public function processB2BRequestCallback(Request $request) :array
            {
                $callbackData 							=	$request->Result;
                $resultCode 							=	$callbackData->ResultCode;
                $resultDesc 							=	$callbackData->ResultDesc;
                $originatorConversationID 				=	$callbackData->OriginatorConversationID;
                $conversationID 						=	$callbackData->ConversationID;
                $transactionID 							=	$callbackData->TransactionID;
                $transactionReceipt						=	$callbackData->ResultParameters->ResultParameter[0]->Value;
                $transactionAmount						=	$callbackData->ResultParameters->ResultParameter[1]->Value;
                $b2CWorkingAccountAvailableFunds		=	$callbackData->ResultParameters->ResultParameter[2]->Value;
                $b2CUtilityAccountAvailableFunds		=	$callbackData->ResultParameters->ResultParameter[3]->Value;
                $transactionCompletedDateTime			=	$callbackData->ResultParameters->ResultParameter[4]->Value;
                $receiverPartyPublicName				=	$callbackData->ResultParameters->ResultParameter[5]->Value;
                $B2CChargesPaidAccountAvailableFunds	=	$callbackData->ResultParameters->ResultParameter[6]->Value;
                $B2CRecipientIsRegisteredCustomer		=	$callbackData->ResultParameters->ResultParameter[7]->Value;

                $result=array(
                    "resultCode"							=>	$resultCode,
                    "resultDesc"							=>	$resultDesc,
                    "originatorConversationID"				=>	$originatorConversationID,
                    "conversationID"						=>	$conversationID,
                    "transactionID"							=>	$transactionID,
                    "transactionReceipt"					=>	$transactionReceipt,
                    "transactionAmount"						=>	$transactionAmount,
                    "b2CWorkingAccountAvailableFunds"		=>	$b2CWorkingAccountAvailableFunds,
                    "b2CUtilityAccountAvailableFunds"		=>	$b2CUtilityAccountAvailableFunds,
                    "transactionCompletedDateTime"			=>	$transactionCompletedDateTime,
                    "receiverPartyPublicName"				=>	$receiverPartyPublicName,
                    "B2CChargesPaidAccountAvailableFunds"	=>	$B2CChargesPaidAccountAvailableFunds,
                    "B2CRecipientIsRegisteredCustomer"		=>	$B2CRecipientIsRegisteredCustomer
                );
                return $result;

            }
        public function processB2CRequestCallback(Request $request) :array
            {

                $callbackData 						= 	$request->Result;
                $resultCode 						=  	$callbackData->ResultCode;
                $resultDesc 						=	$callbackData->ResultDesc;
                $originatorConversationID 			= 	$callbackData->OriginatorConversationID;
                $conversationID 					=	$callbackData->ConversationID;
                $transactionID 						=	$callbackData->TransactionID;
                $initiatorAccountCurrentBalance 	= 	$callbackData->ResultParameters->ResultParameter[0]->Value;
                $debitAccountCurrentBalance 		=	$callbackData->ResultParameters->ResultParameter[1]->Value;
                $amount 							=	$callbackData->ResultParameters->ResultParameter[2]->Value;
                $debitPartyAffectedAccountBalance	=	$callbackData->ResultParameters->ResultParameter[3]->Value;
                $transCompletedTime 				=	$callbackData->ResultParameters->ResultParameter[4]->Value;
                $debitPartyCharges 					= 	$callbackData->ResultParameters->ResultParameter[5]->Value;
                $receiverPartyPublicName 			= 	$callbackData->ResultParameters->ResultParameter[6]->Value;
                $currency							=	$callbackData->ResultParameters->ResultParameter[7]->Value;

                $result=array(
                    "resultCode"						=>	$resultCode,
                    "resultDesc"						=>	$resultDesc,
                    "originatorConversationID"			=>	$originatorConversationID,
                    "conversationID"					=>	$conversationID,
                    "transactionID"						=>	$transactionID,
                    "initiatorAccountCurrentBalance"	=>	$initiatorAccountCurrentBalance,
                    "debitAccountCurrentBalance"		=>	$debitAccountCurrentBalance,
                    "amount"							=>	$amount,
                    "debitPartyAffectedAccountBalance"	=>	$debitPartyAffectedAccountBalance,
                    "transCompletedTime"				=>	$transCompletedTime,
                    "debitPartyCharges"					=>	$debitPartyCharges,
                    "receiverPartyPublicName"			=>	$receiverPartyPublicName,
                    "currency"							=>	$currency
                );


                return $result;
            }
        public function C2BRequestValidation(Request $request) :array
            {
                $callbackData 		=	$request;
                $transactionType 	=	$callbackData->TransactionType;
                $transID 			=	$callbackData->TransID;
                $transTime 			=	$callbackData->TransTime;
                $transAmount 		=	$callbackData->TransAmount;
                $businessShortCode 	=	$callbackData->BusinessShortCode;
                $billRefNumber 		=	$callbackData->BillRefNumber;
                $invoiceNumber 		=	$callbackData->InvoiceNumber;
                $orgAccountBalance 	= 	$callbackData->OrgAccountBalance;
                $thirdPartyTransID 	=	$callbackData->ThirdPartyTransID;
                $MSISDN 			=	$callbackData->MSISDN;
                $firstName 			=	$callbackData->FirstName;
                $middleName 		=	$callbackData->MiddleName;
                $lastName 			=	$callbackData->LastName;

                $result=array(
                    "transTime"			=>	$transTime,
                    "transAmount"		=>	$transAmount,
                    "businessShortCode"	=>	$businessShortCode,
                    "billRefNumber"		=>	$billRefNumber,
                    "invoiceNumber"		=>	$invoiceNumber,
                    "orgAccountBalance"	=>	$orgAccountBalance,
                    "thirdPartyTransID"	=>	$thirdPartyTransID,
                    "MSISDN"			=>	$MSISDN,
                    "firstName"			=>	$firstName,
                    "lastName"			=>	$lastName,
                    "middleName"		=>	$middleName,
                    "transID"			=>	$transID,
                    "transactionType"	=>	$transactionType
                );

               return $result;


            }
        public function processC2BRequestConfirmation(Request $request)
            {
                $callbackData 		=	$request;
                $transactionType 	=	$callbackData->TransactionType;
                $transID 			= 	$callbackData->TransID;
                $transTime 			=	$callbackData->TransTime;
                $transAmount 		=	$callbackData->TransAmount;
                $businessShortCode 	=	$callbackData->BusinessShortCode;
                $billRefNumber 		=	$callbackData->BillRefNumber;
                $invoiceNumber 		=	$callbackData->InvoiceNumber;
                $orgAccountBalance 	=	$callbackData->OrgAccountBalance;
                $thirdPartyTransID 	=	$callbackData->ThirdPartyTransID;
                $MSISDN 			=	$callbackData->MSISDN;
                $firstName 			=	$callbackData->FirstName;
                $middleName 		= 	$callbackData->MiddleName;
                $lastName 			=	$callbackData->LastName;


                $result             =   array(
                    "transTime"			=>	$transTime,
                    "transAmount"		=>	$transAmount,
                    "businessShortCode"	=>	$businessShortCode,
                    "billRefNumber"		=>	$billRefNumber,
                    "invoiceNumber"		=>	$invoiceNumber,
                    "orgAccountBalance"	=>	$orgAccountBalance,
                    "thirdPartyTransID"	=>	$thirdPartyTransID,
                    "MSISDN"			=>	$MSISDN,
                    "firstName"			=>	$firstName,
                    "lastName"			=>	$lastName,
                    "middleName"		=>	$middleName,
                    "transID"			=>	$transID,
                    "transactionType"	=>	$transactionType
                );
                return $result;

            }
        public function processAccountBalanceRequestCallback(Request $request)
            {

                $callbackData               =   $request->Result;
                $resultType                 =   $callbackData->ResultType;
                $resultCode                 =   $callbackData->ResultCode;
                $resultDesc                 =   $callbackData->ResultDesc;
                $originatorConversationID   =   $callbackData->OriginatorConversationID;
                $conversationID             =   $callbackData->ConversationID;
                $transactionID              =   $callbackData->TransactionID;
                $accountBalance             =   $callbackData->ResultParameters->ResultParameter[0]->Value;
                $BOCompletedTime            =   $callbackData->ResultParameters->ResultParameter[1]->Value;

                $result=array(
                    "resultDesc"                  =>$resultDesc,
                    "resultCode"                  =>$resultCode,
                    "originatorConversationID"    =>$originatorConversationID,
                    "conversationID"              =>$conversationID,
                    "transactionID"               =>$transactionID,
                    "accountBalance"              =>$accountBalance,
                    "BOCompletedTime"             =>$BOCompletedTime,
                    "resultType"                  =>$resultType
                );

                return $result;


            }
        public function processReversalRequestCallBack(Request $request)
            {

                $callbackData                       =   $request->Result;
                $data['resultType']                 =   $callbackData->ResultType;
                $data['resultCode']                 =   $callbackData->ResultCode;
                $data['resultDesc']                 =   $callbackData->ResultDesc;
                $data['originatorConversationID']   =   $callbackData->OriginatorConversationID;
                $data['conversationID']             =   $callbackData->ConversationID;
                $data['transactionID']              =   $callbackData->TransactionID;
                return $data;


            }
        public function processSTKPushRequestCallback(Request $request)
            {

                $callbackData               =   $request->Body->stkCallback;
                $data['resultCode']         =   $callbackData->ResultCode;
                $data['resultDesc']         =   $callbackData->ResultDesc;
                $data['merchantRequestID']  =   $callbackData->MerchantRequestID;
                $data['checkoutRequestID']  =   $callbackData->CheckoutRequestID;
                $data['amount']             =   $callbackData->CallbackMetadata->Item[0]->Value;
                $data['mpesaReceiptNumber'] =   $callbackData->CallbackMetadata->Item[1]->Value;
                $data['balance']            =   $callbackData->CallbackMetadata->Item[2]->Value;
                $data['transactionDate']    =   $callbackData->CallbackMetadata->Item[3]->Value;
                $data['phoneNumber']        =   $callbackData->CallbackMetadata->Item[4]->Value;
                return $data;


            }
        public function processSTKPushQueryRequestCallback(Request $request)
            {

                $callbackData 			        =	$request;
                $data['responseCode'] 			=	$callbackData->ResponseCode;
                $data['$responseDescription'] 	=	$callbackData->ResponseDescription;
                $data['merchantRequestID'] 		=	$callbackData->MerchantRequestID;
                $data['checkoutRequestID'] 		=	$callbackData->CheckoutRequestID;
                $data['resultCode'] 			=	$callbackData->ResultCode;
                $data['resultDesc'] 			=	$callbackData->ResultDesc;
                return $data;


            }
        public function processTransactionStatusRequestCallback(Request $request)
            {

                $callbackData                       =   $request->Result;
                $data['resultCode']                 =   $callbackData->ResultCode;
                $data['resultDesc']                 =   $callbackData->ResultDesc;
                $data['originatorConversationID']   =   $callbackData->OriginatorConversationID;
                $data['conversationID']             =   $callbackData->ConversationID;
                $data['transactionID']              =   $callbackData->TransactionID;
                $data['ReceiptNo']                  =   $callbackData->ResultParameters->ResultParameter[0]->Value;
                $data['ConversationID']             =   $callbackData->ResultParameters->ResultParameter[1]->Value;
                $data['FinalisedTime']              =   $callbackData->ResultParameters->ResultParameter[2]->Value;
                $data['Amount']                     =   $callbackData->ResultParameters->ResultParameter[3]->Value;
                $data['TransactionStatus']          =   $callbackData->ResultParameters->ResultParameter[4]->Value;
                $data['ReasonType']                 =   $callbackData->ResultParameters->ResultParameter[5]->Value;
                $data['TransactionReason']          =   $callbackData->ResultParameters->ResultParameter[6]->Value;
                $data['DebitPartyCharges']          =   $callbackData->ResultParameters->ResultParameter[7]->Value;
                $data['DebitAccountType']           =   $callbackData->ResultParameters->ResultParameter[8]->Value;
                $data['InitiatedTime']              =   $callbackData->ResultParameters->ResultParameter[9]->Value;
                $data['OriginatorConversationID']   =   $callbackData->ResultParameters->ResultParameter[10]->Value;
                $data['CreditPartyName']            =   $callbackData->ResultParameters->ResultParameter[11]->Value;
                $data['DebitPartyName']             =   $callbackData->ResultParameters->ResultParameter[12]->Value;
                return $data;
            }

    }