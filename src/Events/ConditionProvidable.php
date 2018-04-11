<?php

namespace Damjangkae\Cart\Events;

use Damjangkae\Cart\CartCondition;

trait ConditionProvidable
{
    public function getCartCondition(): CartCondition
    {
        return $this->cartCondition;
    }
}