<?php

namespace Caydeesoft\Payments\Validation;

use \Illuminate\Support\Facades\Http;

class C2BValidation
{
    public function mpesa($request,$link=null)
    {
        if (is_null($link)) {
            return response()->json(["ResultCode" => 0, "ResultDesc" => "Accepted"]);
        } else {
            $rq = Http::post($link, $request);
            if ($rq->successful()) {
                $response = $rq->object();
                if (property_exists($response, 'error')) {
                    switch ($response->error) {
                        case 'msisdn':
                            return response()->json(["ResultCode" => "C2B00011", "ResultDesc" => "Invalid MSISDN"]);
                        case 'account':
                            return response()->json(["ResultCode" => "C2B00012", "ResultDesc" => "Invalid Account Number"]);
                        case 'amount':
                            return response()->json(["ResultCode" => "C2B00013", "ResultDesc" => "Invalid Amount"]);
                        case 'kyc':
                            return response()->json(["ResultCode" => "C2B00014", "ResultDesc" => "Invalid KYC Details"]);
                        case 'shortcode':
                            return response()->json(["ResultCode" => "C2B00015", "ResultDesc" => "Invalid Shortcode"]);
                        case 'default':
                            return response()->json(["ResultCode" => "C2B00016", "ResultDesc" => "Other errors"]);

                    }
                }
            }
            return response()->json(["ResultCode" => 0, "ResultDesc" => "Accepted"]);

        }
    }
}
