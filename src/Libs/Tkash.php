<?php


namespace Caydeesoft\Payments\Libs;


use Caydeesoft\Payments\Traits\Helper;

class Tkash implements PayChannels
{
    use Helper;
    public $link;

    public function __construct($env)
    {

        if($env == 'production')
        {
            $this->link     =	'https://production.gw.mfs-tkl.com/';

        }
        else
        {
            $this->link     =	'https://staging.gw.mfs-tkl.com/';

        }

    }

    /**
     * @param $plaintext
     * @return void
     */
    public function cert_encrypt($plaintext)
    {

    }

    /**
     * @param $request
     * @return void
     */
    public function stkpush($request)
    {

    }
    /**
     * @param $request
     * @return array|object|void
     */
    public function generate_token($request)
    {
        try
        {
            $credentials    =   base64_encode($request->consumerkey.':'.$request->consumersecret);
            $data           =   Http::withHeaders(['Content-Type'=>'application/json','Authorization'=>'Basic '.$credentials])
                ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
                ->post($this->link.TkashParameters::token_link,['username'=>$request->name,'password'=>$request->password]);

            if($data->successful())
            {
                Log::error("Tkash Token",(array)$data->object());
                return $data->object();
            }
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
            $post_data  = [

                'registerUrlRequest' =>
                    [

                        'consumerId'            => $request->consumer_id ,
                        'notificationUrl'       => TkashParameters::c2bconfirmationcallback() ,
                        'notificationUrlType'   => 'REST' ,
                        'validationUrl'         => TkashParameters::c2bvalidationcallback() ,
                        'validationUrlType'     => 'REST' ,
                        'creationDate'          => date('d-M-y\TH:i:s')
                    ]

            ];
            return $this->invoke_server($this->link.TkashParameters::registerurl , $post_data , $this->generate_token($request));
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
    public function UpdateURL($request)
    {
        try
        {
            $post_data  = [

                'registerUrlRequest' =>
                    [

                        'consumerId'            => $request->consumer_id ,
                        'notificationUrl'       => TkashParameters::c2bconfirmationcallback() ,
                        'notificationUrlType'   => 'REST' ,
                        'validationUrl'         => TkashParameters::c2bvalidationcallback() ,
                        'validationUrlType'     => 'REST' ,
                        'creationDate'          => date('d-M-y\TH:i:s')
                    ]

            ];
            return $this->invoke_server($this->link.TkashParameters::updateURL , $post_data , $this->generate_token($request),'put');
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
    public function replayNotification($request)
    {
        try
        {
            $notificationTypeValues = [ 'ATP' , 'B2C' , 'C2B' , 'B2B' ];

            //Check whether the correct NotificationTypeValues have been set.
            if (!in_array(strtoupper($request->notificationType) , $notificationTypeValues))
            {
                die(json_encode([ 'errorMessage' => 'Error on Request Channel' , 'errorDescription' => 'Notification Type: ' . $notificationType . ' is Unknown. Allowed Types are ATP|B2C|C2B|B2B' ]));
            }
            else
            {
                $params = http_build_query(
                    [
                        'notificationType'  => $request->notificationType,
                        'id'                => $request->consumer_id ,
                        'limit'             => $request->limit
                    ]
                );

                $response = $this->invoke_server( TkashParameters::replayNotification,$params  ,  $this->generate_token($request),"get" );

                return $response;

            }
        }
        catch(HttpException $e)
        {
            Log::error($e->getMessage());
        }
    }


    /**
     * @param $request
     * @return mixed|void
     */
    public function balance($request)
    {
        // TODO: Implement balance() method.
    }

    public function b2c($request)
    {
        // TODO: Implement b2c() method.
    }

    public function b2b($request)
    {
        // TODO: Implement b2b() method.
    }

    public function refund($request)
    {
        // TODO: Implement refund() method.
    }
}
