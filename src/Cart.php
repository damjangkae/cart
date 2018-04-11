<?php

namespace Damjangkae\Cart;

use Illuminate\Support\Collection;
use Damjangkae\Cart\Conditions\ItemAffectable;
use Damjangkae\Cart\Conditions\TotalAffectable;

class Cart
{
    private $items;

    private $conditions;

    private $subtotal;

    private $total;

    public function __construct()
    {
        $this->items = new Collection;
        $this->conditions = new Collection;
        $this->subtotal = 0;
        $this->total = 0;
    }

    public function calculateItems()
    {
        $this->items->each(function(CartItem $cartItem, $key) {
            if (str_contains($key, 'conditionItem_')) {
                $this->items->pull($key);
            }
        });

        $this->conditions
            ->sortBy('priority')
            ->filter(function ($cartCondition) {
                return is_a($cartCondition->getCondition(), ItemAffectable::class);
            })
            ->each(function ($cartCondition) {
                if ($cartCondition->getCondition()->active($this)) {
                    $cartItem = $cartCondition->getCondition()->getItem($this);
                    $this->items->put('conditionItem_' . $cartItem->identifier, $cartItem);
                }
            });
    }

    public function calculateSubtotal()
    {
        $this->subtotal = $this->items->sum(function (CartItem $cartItem) {
            return $cartItem->quantity * $cartItem->price;
        });
    }

    public function calculateTotal()
    {
        $total = $this->subtotal;

        $this->conditions
            ->sortBy('priority')
            ->filter(function ($cartCondition) {
                return is_a($cartCondition->getCondition(), TotalAffectable::class);
            })->each(function (CartCondition $cartCondition) use (&$total) {
                if ($cartCondition->getCondition()->active($this)) {
                    $total -= $cartCondition->getCondition()->getDiscount($this);
                }
            });

        $this->total = $total;
    }

    public function __get($name)
    {
        return $this->{$name};
    }
}