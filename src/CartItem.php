<?php

namespace Damjangkae\Cart;

use Damjangkae\Cart\Contracts\Buyable;

class CartItem
{
    private $identifier;

    private $item;

    private $quantity;

    private $price;

    private $attributes;

    public function __construct(Buyable $item, $quantity = 1, float $price = null, array $attributes = [])
    {
        if (is_array($quantity)) {
            $parameters = $quantity;
            $quantity = array_get($parameters, 'quantity', 1);
            $price = array_get($parameters, 'price', null);
            $attributes = array_get($parameters, 'attributes', []);
        }

        $this->identifier = $item->getIdentifier();
        $this->item = $item;
        $this->quantity = $quantity;
        $this->price = is_null($price) ? $item->getPrice() : $price;
        $this->attributes = $attributes;
    }

    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;
    }

    public function setPrice(float $price)
    {
        $this->price = $price;
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function getSubtotal()
    {
        return $this->price * $this->quantity;
    }

    public function __get($name)
    {
        return $this->{$name};
    }
}