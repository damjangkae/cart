<?php

namespace Damjangkae\Cart;

use Illuminate\Support\Collection;
use Damjangkae\Cart\Contracts\Buyable;
use Damjangkae\Cart\Contracts\Conditionable;

interface CartManagerInterface
{
    /**
     * Set cart instance
     *
     * @param $name
     * @return CartManager
     */
    public function instance($name): CartManager;

    /**
     * Check if cart has item
     *
     * @param $identifier
     * @return bool
     */
    public function has($identifier): bool;

    /**
     * Get cart item by identifier
     *
     * @param $identifier
     * @return CartItem
     */
    public function find($identifier): CartItem;

    /**
     * Get cart's items
     *
     * @return Collection
     */
    public function items(): Collection;

    /**
     * Get cart
     *
     * @return Cart
     */
    public function get(): Cart;

    /**
     * Search item
     *
     * @param callable $callable
     * @return Collection
     */
    public function search(callable $callable): Collection;

    /**
     * Check if cart is empty
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Count item in cart by item
     *
     * @return int
     */
    public function count(): int;

    /**
     * Count item in cart
     *
     * @return int
     */
    public function quantity(): int;

    /**
     * Get total price of items
     *
     * @return float
     */
    public function subtotal(): float;

    /**
     * Get total price of items with additional price applied
     *
     * @return float
     */
    public function total(): float;

    /**
     * Add item into cart
     *
     * @param Buyable $item
     * @param $quantity
     * @param float $price
     * @param array $attributes
     * @return mixed
     */
    public function add(Buyable $item, $quantity = 1, float $price = null, array $attributes = []);

    /**
     * Update item in cart
     *
     * @param $identifier
     * @param $parameters
     * @return mixed
     */
    public function update($identifier, $parameters);

    /**
     * Remove item form cart
     *
     * @param $identifier
     * @return mixed
     */
    public function remove($identifier);

    /**
     * Get collections
     *
     * @return Collection
     */
    public function conditions(): Collection;

    /**
     * Check if collection exists
     *
     * @param $name
     * @return bool
     */
    public function hasCondition($name): bool;

    /**
     * Get condition by name
     *
     * @param $name
     * @return CartCondition
     */
    public function findCondition($name): CartCondition;

    /**
     * Add condition
     *
     * @param string $name
     * @param Conditionable $condition
     * @param int $priority
     * @return mixed
     */
    public function addCondition(string $name, Conditionable $condition, $priority = 0);

    /**
     * Remove condition
     *
     * @param $name
     * @return mixed
     */
    public function removeCondition(string $name);

    /**
     * Refresh conditions
     *
     * @return mixed
     */
    public function refreshConditions();

    /**
     * Destroy cart
     *
     * @return mixed
     */
    public function destroy();

    /**
     * Store cart in database
     *
     * @param string $identifier
     * @throws \Exception
     */
    public function store(string $identifier);

    /**
     * Restore cart from database
     *
     * @param string $identifier
     */
    public function restore(string $identifier);

    /**
     * Truncate cart table
     *
     * @return mixed
     */
    public function truncate();
}