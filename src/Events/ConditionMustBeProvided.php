<?php

namespace Damjangkae\Cart\Events;

use Damjangkae\Cart\CartCondition;

interface ConditionMustBeProvided
{
    public function getCartCondition(): CartCondition;
}