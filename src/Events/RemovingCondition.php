<?php

namespace Damjangkae\Cart\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Damjangkae\Cart\Cart;
use Damjangkae\Cart\CartCondition;

class RemovingCondition implements CartMustBeProvided, ConditionMustBeProvided
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    use CartProvidable, ConditionProvidable;

    public $cart;
    
    public $cartCondition;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Cart $cart, CartCondition $cartCondition)
    {
        $this->cart = $cart;
        $this->cartCondition = $cartCondition;
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
