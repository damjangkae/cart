<?php

namespace Damjangkae\Cart\Events;

use Damjangkae\Cart\Cart;

interface CartMustBeProvided
{
    public function getCart(): Cart;
}