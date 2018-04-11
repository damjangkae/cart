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

class StoringCart implements CartMustBeProvided, IdentifierMustBeProvided
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    use CartProvidable, IdentifierProvidable;

    public $cart;
    public $identifier;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Cart $cart, string $identifier)
    {
        $this->cart = $cart;
        $this->identifier = $identifier;
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
