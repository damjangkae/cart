<?php

namespace Damjangkae\Cart\Tests\Mocks;

use Damjangkae\Cart\Cart;
use Damjangkae\Cart\Conditions\TotalAffectable;

class TenPercentOffIfSubtotalOver500Baht implements TotalAffectable
{
    public function allow(Cart $cart): bool
    {
        return true;
    }

    public function active(Cart $cart): bool
    {
        return $cart->subtotal > 500;
    }

    public function getDiscount(Cart $cart): float
    {
        return $cart->subtotal * .1;
    }
}