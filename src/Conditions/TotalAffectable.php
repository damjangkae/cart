<?php

namespace Damjangkae\Cart\Conditions;

use Damjangkae\Cart\Cart;
use Damjangkae\Cart\Contracts\Conditionable;

interface TotalAffectable extends Conditionable
{
    public function getDiscount(Cart $cart): float;
}
