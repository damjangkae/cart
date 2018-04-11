<?php

namespace Damjangkae\Cart\Exceptions;

use Throwable;

class ConditionIsNotAllowedToAddException extends \Exception
{
    protected $message = 'Condition is not allowed to add.';

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message ?: $this->message, $code, $previous);
    }
}