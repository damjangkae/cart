# Laravel Cart
A simple cart built for Laravel.

## Installation
1) Download this repository and place in `packages`

2) Add the path into `composer.json`
```json
composer require damjangkae/cart
```

3) Add these following lines to `config/app.php`.
- At `providers` array:
```php
Damjangkae\Cart\CartServiceProvider::class
```
- At `aliases` array:
```php
'Cart' => Damjangkae\Cart\Facades\Cart::class
```

4) Do `composer dump-autoload`.

Done.

---

## Overview
Look at one of the following topics to learn more about LaravelShoppingcart

* [Usage](#usage)
* [Collections](#collections)
* [Instances](#instances)
* [Models](#models)
* [Database](#database)
* [Exceptions](#exceptions)
* [Events](#events)

## Usage

Here is the methods provided:

### Cart::add()

```php
$book = Book::find(1);
Cart::add($book);
```

By the model `Book` must be implemented `Damjangkae\Cart\Contracts\Buyable` which required methods:

```php
public function getIdentifier();
public function getPrice(): float;
```

The function `getIdentifier()` expect model to return ID or something that unique your item.

**Another optional way to use `add()`.**

With optional parameters:

```php
Cart::add($book, 2, 100, ['author' => 'John Doe']);
```

or parameters in array as secondary argument:

```php
Cart::add($book, [
    'quantity' => 2,
    'price' => 100,
    'attributes' => ['author' => 'John Doe']
]);
```

### Cart::update()

To update an item in the cart, you must specific the identifier.

```php
$identifier = '9790404436093';
Cart::update($identifier, 2);
```

The existing data will be overwritten with new one by using `update()` which different with `add()` that rely on existing one. Example:

```php
Cart::add($book1, 2);
Cart::update($book1, 3);
// The total will be 3

Cart::add($book2, 2);
Cart::add($book2, 3);
// The total will be 5
```

**Another way to use `update()`.**

Same as you do with `add()`.

```php
Cart::update($identifier, ['price' => 900]);
```

### Cart::remove()

```php
$identifier = '9790404436093';
Cart::remove($identifier);
```

**Notice: Updating item quantity to 0 is share the same result with `remove()`.**

### Cart::find()

Easy as same as `update()` and `remove()`

```php
$identifier = '9790404436093';

Cart::find($identifier);
```

`Damjangkae\Cart\Exceptions\ItemNotFoundException` will be thrown if item not exists.

### Cart::items()

Return collection of items in cart

```php
Cart::items();
```

### Cart::destroy()

Delete the cart object.

```php
Cart::destroy();
```

### Cart::subtotal()

Return the subtotal price.

```php
Cart::subtotal();
```

### Cart::total()

```php
Cart::subtotal();
```

### Cart::count()

Return the item count by type.
**2 books and 3 chairs will return 2**

```php
Cart::count();
```

### Cart::quantity()

Return the item count by all of quantity.
**2 books and 3 chairs will return 5**

```php
Cart::quantity();
```

### Cart::isEmpty()

Return `true` if cart has no items.

```php
Cart::isEmpty();
```

### Cart::search()

Passed closure function and you do your magic.

```php
Cart::search(function (CartItem $cartItem) {
	return $cartItem->price > 1000;
});
```
**Notice: Result returned in collection.**

---

## Collections
Most of methods return as collection (`Illuminate\Support\Collection`)
So you know what to do.

```php
Cart::items()->first();
```

```php
Cart::search(function (CartItem $cartItem) {
	return $cartItem->price > 1000;
})->count();
```

Please see laravel's collection for more info: https://laravel.com/docs/master/collections

---

## Instances
You may wish to have a multiple instance of the cart in same session, here is the solution.
```php
$book1 = Book::find(1);
$book2 = Book::find(2);

Cart::instance('gift')->add($book1);
Cart::instance('wishlist')->add($book2);

Cart::instance('gift')->items()->first(); // book1
Cart::instance('wishlist')->items()->first(); // book2
```

**Notice: If the instance name not set, the default will be `default` prepend with `cart_`. So If you set instance name as `wishlist` the session name will be `cart_whishlist`.**

---

## Conditions
Every e-commerce have a promotion so let the Cart do the magic behind the scene.

#### First: Create your condition class.
If your condition going to affect with **total price** make sure you implement `Damjangkae\Cart\Conditions\TotalAffectable`, but if your condition going to affect with **items** you must implement `Damjangkae\Cart\Conditions\ItemAffectable`.

**Example:** You'd like to have a 10% discount if customer has subtotal more than 500.
```php
<?php

use Damjangkae\Cart\Cart;
use Damjangkae\Cart\Conditions\TotalAffectable;

class TenPercentOffIfSubtotalOver500Baht implements TotalAffectable
{
    public function allow(Cart $cart): bool
    {
        return true;
    }

    public function active(Cart $cart): bool
    {
        return $cart->subtotal > 500;
    }

    public function getDiscount(Cart $cart): float
    {
        return $cart->subtotal * .1;
    }
}
```

As you can see you must implement 3 methods which are:

- `allow()` expect you to return boolean that if `true` returned this condition is allowed to add to the cart. If `false` returned, `Damjangkae\Cart\Exceptions\ConditionIsNotAllowToAddException` will be thrown.

- `active()` expect you to return boolean that if `true` returned this condition is active.

- `getDiscount()` expect you to return float that will be the discount to the subtotal.

**Example:** You'd like to have give a free another book if customer has the book in the cart.
```php
<?php

use Damjangkae\Cart\Cart;
use Damjangkae\Cart\CartItem;
use Damjangkae\Cart\Conditions\ItemAffectable;

class FreeAnotherBookIfTheBookInCart implements ItemAffectable
{
    public function allow(Cart $cart): bool
    {
        return true;
    }

    public function active(Cart $cart): bool
    {
        $book = App\Book::find(1);
        
        return $cart->items->has($book->getItentifier());
    }

    public function getItem(Cart $cart): CartItem
    {
        $anotherBook = App\Book::find(2);
        
        return new CartItem($anotherBook, 1, 0);
    }
}
```

The additional method `getItem` expect you to return `Damjangkae\Cart\CartItem` which the way to create is same with you do on `Cart::add()`. **Make sure you set the price to 0 if you'd like to give it for free.**

**Last example:** If you wish to have only 1 condition in the cart.

```php
public function allow(Cart $cart): bool
{
    return $cart->conditions->count() == 0;
}
```

#### Sectond: Add your condition to cart.
You have to provide condition key like `10% off` to identify your condition.

```php
Cart::addCondition('10% off', new TenPercentOffIfSubtotalOver500Baht);
```

#### Final: See the magic with your own eyes.
```php
Cart::add($book, 1, 1000);
Cart::addCondition('10% off', new TenPercentOffIfSubtotalOver500Baht);

Cart::subtotal(); // 1000
Cart::total(); // 900
```

---

## Store in Database
You may wish to store data into database for example user like to shopping over machine.

#### Storing
```php
\Cart::add($book1);
\Cart::addCondition('10% off', new TenPercentOffIfSubtotalOver500Baht);

\Cart::store('user_1');
```

#### Restoring
```php
\Cart::restore('user_1');

\Cart::items()->first(); // $book1
```
**Notice: After restored the record in database will be deleted**

#### Also work with instance

```php
\Cart::instance('user_1')->add($book1);
\Cart::instance('user_2')->add($book2);

\Cart::instance('user_1')->store('test_1');
\Cart::instance('user_2')->store('test_2');
```

---

## Exceptions

| Exception | Reason |
| --- | --- |
| *ItemNotFoundException* | When trying to retrieve not exists item |
| *ConditionIsNotAllowedToAddException* | When try to add condition which `false` returned at method `allowed()` |
| *ConditionNotFoundException* | When trying to retrieve not exists condition |
| *CartAlreadyStoredException* | When trying to stored cart with exists identofier |
| *CartNotFoundInStoreException* | When trying to retrieve not exists cart in database |

---

## Events

| Event (Class) | Parameter |
| --- | --- |
| Damjangkae\Cart\Events\AddingItem | `Cart`, `CartItem` |
| Damjangkae\Cart\Events\AddedItem | `Cart`, `CartItem` |
| Damjangkae\Cart\Events\UpdatingItem | `Cart`, `CartItem`, `$parameters` |
| Damjangkae\Cart\Events\UpdatedItem | `Cart`, `CartItem` |
| Damjangkae\Cart\Events\RemovingItem | `Cart`, `CartItem` |
| Damjangkae\Cart\Events\RemovedItem | `Cart`, `CartItem` |
| Damjangkae\Cart\Events\AddingCondition | `Cart`, `CartCondition` |
| Damjangkae\Cart\Events\AddedCondition | `Cart`, `CartCondition` |
| Damjangkae\Cart\Events\RemovingCondition | `Cart`, `CartCondition` |
| Damjangkae\Cart\Events\RemovedCondition | `Cart`, `CartCondition` |
| Damjangkae\Cart\Events\RefreshedCondition | `Cart` |
| Damjangkae\Cart\Events\DestroyingCart | `Cart` |
| Damjangkae\Cart\Events\DestroyedCart | `Cart` |
| Damjangkae\Cart\Events\StoringCart | `Cart`, `$identifier` |
| Damjangkae\Cart\Events\StoredCart | `Cart`, `$identifier` |
| Damjangkae\Cart\Events\RestoringCart | `$identifier` |
| Damjangkae\Cart\Events\RestoredCart | `$identifier` |
