<?php
namespace Caydeesoft\Payments\Callbacks;

use Illuminate\Http\Request;

class Callbacks
    {
        public  $prov;
        public function __construct(CallbackInterface $provider)
            {
                $this->prov = $provider;
            }

        /**
         * @param Request $request
         * @return mixed
         */
        public function processB2BRequestCallback(Request $request)
            {
                return $this->prov->processB2BRequestCallback($request);
            }
    }