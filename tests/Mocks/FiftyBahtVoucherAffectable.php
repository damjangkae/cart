<?php

namespace Damjangkae\Cart\Tests\Mocks;

use Damjangkae\Cart\Cart;
use Damjangkae\Cart\CartCondition;
use Damjangkae\Cart\Conditions\TotalAffectable;

class FiftyBahtVoucher implements TotalAffectable
{
    public function allow(Cart $cart): bool
    {
        return !$cart->conditions->filter(function(CartCondition $cartCondition) {
            return get_class($cartCondition->getCondition()) == self::class;
        })->count();
    }

    public function active(Cart $cart): bool
    {
        return true;
    }

    public function getDiscount(Cart $cart): float
    {
        return 50;
    }
}