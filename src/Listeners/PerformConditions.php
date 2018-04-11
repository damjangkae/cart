<?php

namespace Damjangkae\Cart\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Damjangkae\Cart\Events\CartMustBeProvided;

class PerformConditions
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
    public function handle(CartMustBeProvided $event)
    {
        $cart = $event->getCart();

        $cart->calculateItems();
        $cart->calculateSubtotal();
        $cart->calculateTotal();
    }
}
