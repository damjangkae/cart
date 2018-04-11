<?php

namespace Damjangkae\Cart\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Damjangkae\Cart\Events\CartItemMustBeProvided;

class ThrowExceptionIfTryToAddZeroQuantity
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CartItemMustBeProvided  $event
     * @return void
     */
    public function handle(CartItemMustBeProvided $event)
    {
        $cartItem = $event->getCartItem();

        if ($cartItem->quantity == 0) {
            throw new \InvalidArgumentException('Quantity must not be 0.');
        }
    }
}
