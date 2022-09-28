<?php

namespace Caydeesoft\Payments\Facade;

use Illuminate\Support\Facades\Facade;

class Payments extends Facade
    {
        protected static function getFacadeAccessor() { return 'payment'; }
    }
