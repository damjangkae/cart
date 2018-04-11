<?php

namespace Damjangkae\Cart\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Damjangkae\Cart\Cart;
use Damjangkae\Cart\CartItem;

class RemovedItem implements CartMustBeProvided, CartItemMustBeProvided
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    use CartProvidable, CartItemProvidable;

    public $cart;

    public $cartItem;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Cart $cart, CartItem $cartItem)
    {
        $this->cart = $cart;
        $this->cartItem = $cartItem;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
