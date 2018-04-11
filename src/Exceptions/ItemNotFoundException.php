<?php

namespace Damjangkae\Cart\Exceptions;

use Throwable;

class ItemNotFoundException extends \Exception
{
    protected $message = 'Could not find item in cart.';

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message ?: $this->message, $code, $previous);
    }
}