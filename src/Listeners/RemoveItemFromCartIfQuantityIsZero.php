<?php

namespace Damjangkae\Cart\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Damjangkae\Cart\Events\CartItemMustBeProvided;

class RemoveItemFromCartIfQuantityIsZero
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
     * @param  AddedItemToCart  $event
     * @return void
     */
    public function handle(CartItemMustBeProvided $event)
    {
        $cartItem = $event->getCartItem();

        if ($cartItem->quantity <= 0) {
            \Cart::remove($cartItem->identifier);
        }
    }
}
