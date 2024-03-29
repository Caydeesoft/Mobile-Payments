<?php
namespace Caydeesoft\Payments\Libs;

use App\Constants\MpesaParameters;
use Caydeesoft\Payments\Traits\Helper;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Mpesa extends MpesaParameters implements Paychannels
	{
    use Helper;
    public   $link, $cert,$timestamp;
    public  function __construct($env = 'production')
    {
        $this->timestamp = date('YmdHis');

        if($env == 'production')
        {
            $this->link     =	'https://api.safaricom.co.ke';
            $this->cert     =   app_path('Resources/Mpesa_public_cert.cer');
        }
        else
        {
            $this->link     =	'https://sandbox.safaricom.co.ke';
            $this->cert     =   app_path('Resources/Mpesa_public_sandbox_cert.cer');
        }
    }
    public function generate_token($request)
    {
        try
        {
            $credentials    =   base64_encode($request->consumerkey.':'.$request->consumersecret);
            $data           =   Http::withHeaders(['Content-Type'=>'application/json','Authorization'=>'Basic '.$credentials])
                ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
                ->get($this->link.self::token_link);

            if($data->successful())
            {
                return $data->object();
            }

        }
        catch(HttpException $e)
        {
            Log::error($e->getMessage());
        }
    }

    /**
     * @param $plaintext
     * @return string
     */
    public function cert_encrypt($plaintext)
    {
        $cert       =   $this->cert;
        $fp         =   fopen($cert,"r");
        $publicKey  = fread($fp,filesize($cert));
        fclose($fp);
        openssl_get_publickey($publicKey);
        openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        return  base64_encode($encrypted);
    }

    /**
     * @param $type
     * @return int
     */
    public function getIdentifier($type)
    {
        $type=strtolower($type);
        switch($type)
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
                $x =    4;
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
            $password 	=	base64_encode($request->shortcode.$request->passkey.$this->timestamp);
            $type       =   ($request->type == 'TILL')?'CustomerBuyGoodsOnline':'CustomerPayBillOnline';
            $shortcode  =   ($request->type == 'TILL')?$request->cshortcode:$request->shortcode;
            $data       =   [
                'BusinessShortCode' =>  $request->shortcode,
                'Password'          =>  $password ,
                'Timestamp'         =>  $this->timestamp ,
                'TransactionType'   =>  $type ,
                'Amount'            =>  $request->amount,
                'PartyA'            =>  $request->msisdn ,
                'PartyB'            =>  $shortcode ,
                'PhoneNumber'       =>  $request->msisdn ,
                'CallBackURL'       =>  self::stkrequestcallback() ,
                'AccountReference'  =>  $request->ref ,
                'TransactionDesc'   =>  $request->desc
            ];
            $token      =   $this->generate_token($request);
            if(property_exists($token,'access_token'))
            {
                return $this->invoke_server($this->link.self::checkout_processlink,$data,$token->access_token);
            }

        }
        catch(HttpException $e)
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
            $password 	=	base64_encode($request->shortcode.$request->passkey.$this->timestamp);
            $data        =   [
                'BusinessShortCode' =>  $request->shortcode,
                'Password'          =>  $password ,
                'Timestamp'         =>  $this->timestamp ,
                'CheckoutRequestID' => $request->CheckoutRequestID
            ];
            return $this->invoke_server($this->link.self::checkout_querylink,$data,$this->generate_token($request));
        }
        catch(HttpException $e)
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
                'Initiator'                 =>  $request->initiator,
                'SecurityCredential'        =>  $this->cert_encrypt($request->credential) ,
                'CommandID'                 =>  'TransactionReversal' ,
                'TransactionID'             =>  $request->TransID ,
                'Amount'                    =>  $request->amount ,
                'ReceiverParty'             =>  $request->receiver ,
                'RecieverIdentifierType'    =>  $this->getIdentifier($request->receiverType) ,
                'ResultURL'                 =>  self::reversalURL() ,
                'QueueTimeOutURL'           =>  self::reversalURL() ,
                'Remarks'                   =>  $request->remarks ,
                'Occasion'                  =>  $request->ocassion
            ];
            return $this->invoke_server($this->link.self::reversal_link,$data,$this->generate_token($request)->access_token);
        }
        catch(HttpException $e)
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
                'Initiator'             =>  $request->initiator ,
                'SecurityCredential'    =>  $this->cert_encrypt($request->credential) ,
                'CommandID'             =>  'AccountBalance' ,
                'PartyA'                =>  $request->shortcode ,
                'IdentifierType'        =>  $this->getIdentifier($request->type) ,
                'Remarks'               =>  $request->remark ,
                'QueueTimeOutURL'       =>  self::accountbalcallback() ,
                'ResultURL'             =>  self::accountbalcallback()
            ];

            return $this->invoke_server($this->link.self::balance_link,$data,$this->generate_token($request)->access_token);
        }
        catch(HttpException $e)
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
                'ValidationURL'     =>  self::c2bvalidationcallback(),
                'ConfirmationURL'   =>  self::c2bconfirmationcallback(),
                'ResponseType '     =>  'Canceled',
                'ShortCode'         =>  $request->shortcode
            ];

            return $this->invoke_server($this->link.self::c2b_regiterUrl,$data,$this->generate_token($request)->access_token);
        }
        catch(HttpException $e)
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
            $data =  [
                'Initiator'                 =>  $request->initiator ,
                'SecurityCredential'        =>  $this->cert_encrypt($request->credential) ,
                'CommandID'                 =>  $request->CommandID ,
                'SenderIdentifierType'      =>  $this->getIdentifier($request->sender_type) ,
                'RecieverIdentifierType'    =>  $this->getIdentifier($request->receiver_type) ,
                'Amount'                    =>  $request->amount ,
                'PartyA'                    =>  $request->from ,
                'PartyB'                    =>  $request->to ,
                'AccountReference'          =>  $request->accountref ,
                'Remarks'                   =>  $request->remarks,
                'QueueTimeOutURL'           =>  self::b2bcallback() ,
                'ResultURL'                 => self::b2bcallback()
            ];
            return $this->invoke_server($this->link.self::b2b_link,$data,$this->generate_token($request)->access_token);
        }
        catch(HttpException $e)
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
                'InitiatorName'         =>  $request->initiator ,
                'SecurityCredential'    =>  $this->cert_encrypt($request->credential) ,
                'CommandID'             =>  $request->CommandID ,
                'Amount'                =>  $request->amount ,
                'PartyA'                =>  $request->shortcode ,
                'PartyB'                =>  $request->msisdn ,
                'Remarks'               =>  $request->remarks ,
                'QueueTimeOutURL'       =>  self::b2ccallback() ,
                'ResultURL'             =>  self::b2ccallback() ,
                'Occasion'              =>  $request->ocassion
            ];
            return $this->invoke_server($this->link.self::b2c_link,$data,$this->generate_token($request)->access_token);
        }
        catch(HttpException $e)
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
                'Initiator'                 =>  $request->initiator ,
                'SecurityCredential'        =>  $this->cert_encrypt($request->credential) ,
                'CommandID'                 =>  'TransactionStatusQuery' ,
                'TransactionID'             =>  $request->transID ,
                'PartyA'                    =>  $request->msisdn,
                'IdentifierType'            =>  $this->getIdentifier($request->identifier) ,
                'ResultURL'                 =>  self::transtatURL() ,
                'QueueTimeOutURL'           =>  self::transtatURL() ,
                'Remarks'                   =>  $request->remarks ,
                'Occasion'                  =>  $request->ocassion ,
                'OriginalConversationID'    =>  $request->conversionID
            ];


            return $this->invoke_server($this->link.self::transtat_link,$data,$this->generate_token($request)->access_token);
        }
        catch(HttpException $e)
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
                "QRVersion"     =>  $request->qrversion,
                "TrxCode"       =>  $request->trxcode,//BG,WA,PB,SM,SB
                "CPI"           =>  $request->cpi,
                "MerchantName"  =>  $request->merchantname,
                "Amount"        =>  $request->amount,
                "RefNo"         =>  $request->refno,
                "QRFormat"      =>  $request->qrformat, //1: image, 2: QR String, 3: Binary, 4: PDF
                "QRType"        =>  $request->qrtype // S : Static, D : Dynamic
            ];


            return $this->invoke_server($this->link.self::qrcode,$data,$this->generate_token($request)->access_token);
        }
        catch(HttpException $e)
        {
            Log::error($e->getMessage());
        }
    }
    public function billManagerOptin($request , $state = 0)
        {
            try
            {
                $data = [
                    "shortcode"=>$request->shortcode,
                    "logo"=> $request->logo,
                    "email"=> $request->email,
                    "officialContact"=> $request->msisdn,
                    "sendReminders"=> 1,
                    "callbackUrl"=> self::billManagerOptinURL()
                ];
                $link = ($state == 0)?self::billMOptinLink:self::billMChangeOptinLink;

                return $this->invoke_server($this->link.$link,$data,$this->generate_token($request)->access_token);
            }
            catch(HttpException $e)
            {
                Log::error($e->getMessage());
            }
        }
    public function billManagerSingleInvoice($request)
        {
            try
            {
                $data = [
                    "externalReference"=> $request->invoice_no,
                    "billedFullName"=> $request->name,
                    "billedPhoneNumber"=> $request->msisdn,
                    "billedPeriod"=> $request->billingPeriod,
                    "invoiceName"=> $request->invoice_name,
                    "dueDate"=> $request->due_date,
                    "accountReference"=> $request->ref,
                    "amount"=> $request->amount,
                    "invoiceItems" => ["itemName" => $request->item_name,"amount"=>$request->item_amount]
                ];


                return $this->invoke_server($this->link.self::billMSingleInvoice,$data,$this->generate_token($request)->access_token);
            }
            catch(HttpException $e)
            {
                Log::error($e->getMessage());
            }
        }
    public function billManagerCancelSingleInvoice($request,$data)
        {
            try
                {

                    return $this->invoke_server($this->link.self::billMCancelSingleIn,$data,$this->generate_token($request)->access_token);
                }
            catch(HttpException $e)
                {
                    Log::error($e->getMessage());
                }
        }
	}
