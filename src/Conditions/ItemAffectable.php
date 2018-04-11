<?php

namespace Damjangkae\Cart\Conditions;

use Damjangkae\Cart\Cart;
use Damjangkae\Cart\CartItem;
use Damjangkae\Cart\Contracts\Conditionable;

interface ItemAffectable extends Conditionable
{
    public function getItem(Cart $cart): CartItem;
}
