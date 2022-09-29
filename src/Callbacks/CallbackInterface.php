<?php
namespace Caydeesoft\Payments\Callbacks;

use Illuminate\Http\Request;

interface CallbackInterface
    {
        public function processB2BRequestCallback(Request $request);
    }