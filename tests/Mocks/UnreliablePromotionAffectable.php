<?php

namespace Damjangkae\Cart\Tests\Mocks;

use Damjangkae\Cart\Cart;
use Damjangkae\Cart\Conditions\TotalAffectable;

class UnreliablePromotion implements TotalAffectable
{
    private static $isFriday = false;

    public function allow(Cart $cart): bool
    {
        return true;
    }

    public function active(Cart $cart): bool
    {
        static::$isFriday = !static::$isFriday;

        return static::$isFriday;
    }

    public function getDiscount(Cart $cart): float
    {
        return $cart->subtotal * .5;
    }
}