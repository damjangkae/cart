<?php

namespace Damjangkae\Cart\Tests\Mocks;

use Damjangkae\Cart\Cart;
use Damjangkae\Cart\CartItem;
use Damjangkae\Cart\Conditions\ItemAffectable;

class FreeAnotherBookIfTheBookInCart implements ItemAffectable
{
    public $anotherBook;

    public function __construct($anotherBook)
    {
        $this->anotherBook = $anotherBook;
    }

    public function allow(Cart $cart): bool
    {
        return true;
    }

    public function active(Cart $cart): bool
    {
        return $cart->items->has(123);
    }

    public function getItem(Cart $cart): CartItem
    {
        return new CartItem($this->anotherBook, 1, 0);
    }
}