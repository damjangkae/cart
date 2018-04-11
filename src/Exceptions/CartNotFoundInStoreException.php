<?php

namespace Damjangkae\Cart\Exceptions;

use Throwable;

class CartNotFoundInStoreException extends \Exception
{
    protected $message = 'Cart not found in store.';

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message ?: $this->message, $code, $previous);
    }
}