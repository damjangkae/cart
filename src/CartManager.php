<?php

namespace Damjangkae\Cart;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Events\Dispatcher;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Collection;
use Damjangkae\Cart\Contracts\Buyable;
use Damjangkae\Cart\Contracts\Conditionable;
use Damjangkae\Cart\Events\AddedCondition;
use Damjangkae\Cart\Events\AddedItem;
use Damjangkae\Cart\Events\AddingCondition;
use Damjangkae\Cart\Events\AddingItem;
use Damjangkae\Cart\Events\DestroyedCart;
use Damjangkae\Cart\Events\DestroyingCart;
use Damjangkae\Cart\Events\RefreshedConditions;
use Damjangkae\Cart\Events\RemovedCondition;
use Damjangkae\Cart\Events\RemovedItem;
use Damjangkae\Cart\Events\RemovingCondition;
use Damjangkae\Cart\Events\RemovingItem;
use Damjangkae\Cart\Events\RestoredCart;
use Damjangkae\Cart\Events\RestoringCart;
use Damjangkae\Cart\Events\StoredCart;
use Damjangkae\Cart\Events\StoringCart;
use Damjangkae\Cart\Events\Truncated;
use Damjangkae\Cart\Events\UpdatedItem;
use Damjangkae\Cart\Events\UpdatingItem;
use Damjangkae\Cart\Exceptions\CartAlreadyStoredException;
use Damjangkae\Cart\Exceptions\CartNotFoundinStoreException;
use Damjangkae\Cart\Exceptions\ConditionNotFoundException;
use Damjangkae\Cart\Exceptions\ItemNotFoundException;

class CartManager implements CartManagerInterface
{
    /**
     * @var SessionManager
     */
    private $session;

    /**
     * @var Dispatcher
     */
    private $event;

    /**
     * @var mixed
     */
    protected $modelCart;

    /**
     * @var mixed
     */
    protected $modelCartItem;

    /**
     * @var string
     */
    protected $prefixInstance = 'cart_';

    /**
     * @var string
     */
    protected $instance = 'default';

    /**
     * Cart constructor.
     * @param SessionManager $sessionManager
     * @param Dispatcher $dispatcher
     */
    public function __construct(SessionManager $sessionManager, Dispatcher $dispatcher)
    {
        $this->session = $sessionManager;
        $this->event = $dispatcher;

        $this->modelCart = config('cart.model.cart');
        $this->modelCartItem = config('cart.model.cartItem');
    }

    /**
     * Set cart instance
     *
     * @param string $name
     * @return CartManager
     */
    public function instance($name): self
    {
        $this->instance = $name;

        return $this;
    }

    /**
     * Check if cart has item
     *
     * @param $identifier
     * @return bool
     */
    public function has($identifier): bool
    {
        return $this->items()->has($identifier);
    }

    /**
     * Get cart item by identifier
     *
     * @param $identifier
     * @return CartItem
     * @throws ItemNotFoundException
     */
    public function find($identifier): CartItem
    {
        if (!$this->has($identifier)) {
            throw new ItemNotFoundException;
        }

        return $this->items()->get($identifier);
    }

    /**
     * Get cart's items
     *
     * @return Collection
     */
    public function items(): Collection
    {
        return $this->get()->items;
    }

    /**
     * Get cart
     *
     * @return Cart
     */
    public function get(): Cart
    {
        return $this->session->has($this->getCartInstanceName()) ?
            $this->session->get($this->getCartInstanceName()) : new Cart;
    }

    /**
     * Search item
     *
     * @param callable $callable
     * @return Collection
     */
    public function search(callable $callable): Collection
    {
        return $this->items()->filter(function (CartItem $cartItem) use ($callable) {
            return $callable($cartItem);
        });
    }

    /**
     * Check if cart is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return !$this->count();
    }

    /**
     * Count item in cart by item
     *
     * @return int
     */
    public function count(): int
    {
        return $this->items()->count();
    }

    /**
     * Count item in cart
     *
     * @return int
     */
    public function quantity(): int
    {
        return $this->items()->sum(function (CartItem $cartItem) {
            return $cartItem->quantity;
        });
    }

    /**
     * Get total price of items
     *
     * @return float
     */
    public function subtotal(): float
    {
        return $this->get()->subtotal;
    }

    /**
     * Get total price of items with additional price applied
     *
     * @return float
     */
    public function total(): float
    {
        return $this->get()->total;
    }

    /**
     * Add item into cart
     *
     * @param Buyable $item
     * @param int $quantity
     * @param float|null $price
     * @param array $attributes
     * @return CartItem|mixed
     */
    public function add(Buyable $item, $quantity = 1, float $price = null, array $attributes = [])
    {
        $cart = $this->get();
        $identifier = $item->getIdentifier();

        $cartItem = $this->createCartItem($item, $quantity, $price, $attributes);

        $this->event->fire(new AddingItem($cart, $cartItem));

        if ($this->has($identifier)) {
            return $this->update($identifier, [
                'quantity' => $this->find($identifier)->quantity + $quantity
            ]);
        } else {
            $cart->items->put($identifier, $cartItem);
        }

        $this->event->fire(new AddedItem($cart, $cartItem));

        $this->session->put($this->getCartInstanceName(), $cart);

        return $cartItem;
    }

    /**
     * Update item in cart
     *
     * @param $identifier
     * @param $parameters
     * @return CartItem
     */
    public function update($identifier, $parameters)
    {
        $cart = $this->get();
        $cartItem = $this->find($identifier);

        $this->event->fire(new UpdatingItem($cart, $cartItem, $parameters));

        if (is_array($parameters)) {
            if (array_has($parameters, 'quantity')) {
                $cartItem->setQuantity(array_get($parameters, 'quantity'));
            }

            if (array_has($parameters, 'price')) {
                $cartItem->setPrice(array_get($parameters, 'price'));
            }

            if (array_has($parameters, 'attributes')) {
                $cartItem->setAttributes(array_get($parameters, 'attributes'));
            }
        } else {
            $cartItem->setQuantity($parameters);
        }

        $cart->items->put($identifier, $cartItem);

        $this->event->fire(new UpdatedItem($cart, $cartItem));

        $this->session->put($this->getCartInstanceName(), $cart);

        return $cartItem;
    }

    /**
     * Remove item form cart
     *
     * @param $identifier
     * @return CartItem
     */
    public function remove($identifier)
    {
        $cart = $this->get();
        $cartItem = $this->find($identifier);

        $this->event->fire(new RemovingItem($cart, $cartItem));

        $cart->items->pull($identifier);

        $this->event->fire(new RemovedItem($cart, $cartItem));

        $this->session->put($this->getCartInstanceName(), $cart);

        return $cartItem;
    }

    public function refresh()
    {
        $cart = $this->get();

        $this->event->fire(new RefreshedConditions($cart));
    }

    /**
     * Get conditions
     *
     * @return Collection
     */
    public function conditions(): Collection
    {
        return $this->get()->conditions;
    }

    /**
     * Check if collection exists
     *
     * @param $name
     * @return bool
     */
    public function hasCondition($name): bool
    {
        return $this->conditions()->has($name);
    }

    /**
     * Get condition by name
     *
     * @param $name
     * @return CartCondition
     * @throws ConditionNotFoundException
     */
    public function findCondition($name): CartCondition
    {
        if (!$this->hasCondition($name)) {
            throw new ConditionNotFoundException;
        }

        return $this->conditions()->get($name);
    }

    /**
     * Add condition
     *
     * @param string $name
     * @param Conditionable $condition
     * @param int $priority
     * @return mixed
     */
    public function addCondition(string $name, Conditionable $condition, $priority = 0)
    {
        $cart = $this->get();

        $cartCondition = $this->createCondition($condition, $priority);

        $this->event->fire(new AddingCondition($cart, $cartCondition));

        $cart->conditions->put($name, $cartCondition);

        $this->event->fire(new AddedCondition($cart, $cartCondition));

        $this->session->put($this->getCartInstanceName(), $cart);
    }

    /**
     * Remove condition
     *
     * @param string $name
     * @return mixed
     */
    public function removeCondition(string $name)
    {
        $cart = $this->get();
        $cartCondition = $this->conditions()->get($name);

        $this->event->fire(new RemovingCondition($cart, $cartCondition));

        $cart->conditions->pull($name);

        $this->event->fire(new RemovedCondition($cart, $cartCondition));

        $this->session->put($this->getCartInstanceName(), $cart);
    }

    /**
     * Refresh conditions
     */
    public function refreshConditions()
    {
        $cart = $this->get();

        $this->event->fire(new RefreshedConditions($cart));
    }

    /**
     * Destroy Cart
     */
    public function destroy()
    {
        $cart = $this->get();

        $this->event->fire(new DestroyingCart($cart));

        $this->session->forget($this->getCartInstanceName());

        $this->event->fire(new DestroyedCart($cart));
    }

    /**
     * Store cart in database
     *
     * @param string $identifier
     * @throws \Exception
     */
    public function store(string $identifier)
    {
        $modelCart = new $this->modelCart;
        $exists = $modelCart::where('identifier', $identifier)->exists();

        if ($exists) {
            throw new CartAlreadyStoredException;
        }

        $cart = $this->get();

        $this->event->fire(new StoringCart($cart, $identifier));

        $modelCart = new $this->modelCart;
        $modelCart->identifier = $identifier;
        $modelCart->instance = $this->instance;
        $modelCart->content = serialize($cart);
        $modelCart->save();

        $this->event->fire(new StoredCart($cart, $identifier));
    }

    /**
     * Restore cart from database
     *
     * @param string $identifier
     * @throws CartNotFoundinStoreException
     */
    public function restore(string $identifier)
    {
        $this->event->fire(new RestoringCart($identifier));

        $modelCart = new $this->modelCart;

        $query = $modelCart::where('identifier', $identifier);

        try {
            $cart = $query->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            throw new CartNotFoundinStoreException;
        }

        $this->session->put('cart_' . $cart->instance, unserialize($cart->content));

        $query->delete();

        $this->event->fire(new RestoredCart($cart));
    }

    /**
     * Truncate cart table
     */
    public function truncate()
    {
        $this->modelCart::truncate();

        $this->event->fire(new Truncated);
    }

    /**
     * Create cartItem
     *
     * @param Buyable $item
     * @param int $quantity
     * @param float|null $price
     * @param array $attributes
     * @return CartItem
     */
    private function createCartItem(Buyable $item, $quantity = 1, float $price = null, array $attributes = []): CartItem
    {
        return new CartItem($item, $quantity, $price, $attributes);
    }

    /**
     * Create condition
     *
     * @param Conditionable $condition
     * @param int $priority
     * @return CartCondition
     */
    private function createCondition(Conditionable $condition, int $priority = 0)
    {
        return new CartCondition($condition, $priority);
    }

    /**
     * Prepare instance name
     *
     * @param string|null $instance
     * @return string
     */
    private function getCartInstanceName(string $instance = null): string
    {
        $instance = $instance ?: $this->instance;

        return $this->prefixInstance . $instance;
    }
}