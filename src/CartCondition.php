<?php

namespace Damjangkae\Cart;

use Damjangkae\Cart\Contracts\Conditionable;

class CartCondition
{
    private $condition;

    protected $priority;

    public function __construct(Conditionable $condition, int $priority)
    {
        $this->condition = $condition;
        $this->priority = $priority;
    }

    /**
     * @return Conditionable
     */
    public function getCondition(): Conditionable
    {
        return $this->condition;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

}