<?php

namespace Damjangkae\Cart\Tests\Mocks;

use Damjangkae\Cart\Contracts\Buyable;

class Book implements Buyable
{
    private $isbn;

    private $title;

    private $price;

    public function __construct($isbn, $title, $price)
    {
        $this->isbn = $isbn;
        $this->title = $title;
        $this->price = $price;
    }

    public function getIdentifier()
    {
        return $this->isbn;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price)
    {
        $this->price = $price;
    }

}