<?php

namespace Damjangkae\Cart\Events;

use Damjangkae\Cart\CartItem;

interface CartItemMustBeProvided
{
    public function getCartItem(): CartItem;
}