<?php

namespace Damjangkae\Cart\Contracts;

interface Buyable
{
    public function getIdentifier();

    public function getPrice(): float;
}