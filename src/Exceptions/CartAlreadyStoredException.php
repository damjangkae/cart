<?php

namespace Damjangkae\Cart\Exceptions;

use Throwable;

class CartAlreadyStoredException extends \Exception
{
    protected $message = 'Cart already stored.';

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message ?: $this->message, $code, $previous);
    }
}