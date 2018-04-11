<?php

namespace Damjangkae\Cart\Facades;

use Illuminate\Support\Facades\Facade;
use Damjangkae\Cart\CartManagerInterface;

class Cart extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return CartManagerInterface::class;
    }
}
