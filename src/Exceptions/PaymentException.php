<?php

namespace Caydeesoft\Payments\Exceptions;


use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentException extends HttpException
{
        public function notification()
            {
                return new static(401, 'Notification Failed', null, []);
            }
    }
