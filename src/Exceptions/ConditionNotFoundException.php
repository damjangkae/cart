<?php

namespace Damjangkae\Cart\Exceptions;

use Throwable;

class ConditionNotFoundException extends \Exception
{
    protected $message = 'Could not find condition in cart.';

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message ?: $this->message, $code, $previous);
    }
}