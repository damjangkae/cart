<?php

namespace Damjangkae\Cart\Events;

use Damjangkae\Cart\Cart;

trait CartProvidable
{
    public function getCart(): Cart
    {
        return $this->cart;
    }
}