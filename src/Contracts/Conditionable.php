<?php

namespace Damjangkae\Cart\Contracts;

use Damjangkae\Cart\Cart;

interface Conditionable
{
    public function allow(Cart $cart): bool;

    public function active(Cart $cart): bool;
}