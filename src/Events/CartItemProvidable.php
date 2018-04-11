<?php

namespace Damjangkae\Cart\Events;

use Damjangkae\Cart\CartItem;

trait CartItemProvidable
{
    public function getCartItem(): CartItem
    {
        return $this->cartItem;
    }
}