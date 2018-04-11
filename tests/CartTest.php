<?php

namespace Damjangkae\Cart\Tests;

use Illuminate\Support\Collection;
use Damjangkae\Cart\Cart;
use Damjangkae\Cart\CartCondition;
use Damjangkae\Cart\CartItem;
use Damjangkae\Cart\CartServiceProvider;
use Damjangkae\Cart\Exceptions\ConditionIsNotAllowedToAddException;
use Damjangkae\Cart\Exceptions\ConditionNotFoundException;
use Damjangkae\Cart\Exceptions\ItemNotFoundException;
use Damjangkae\Cart\Tests\Mocks\Book;
use Damjangkae\Cart\Tests\Mocks\FiftyBahtVoucher;
use Damjangkae\Cart\Tests\Mocks\FreeAnotherBookIfTheBookInCart;
use Damjangkae\Cart\Tests\Mocks\TenPercentOffIfSubtotalOver500Baht;
use Damjangkae\Cart\Tests\Mocks\UnreliablePromotion;
use Orchestra\Testbench\TestCase;

class CartTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [CartServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Cart' => \Damjangkae\Cart\Facades\Cart::class
        ];
    }

    public function testInstanceCart()
    {
        $book1 = new Book(123, 'The Book 1', 1000);
        $book2 = new Book(456, 'The Book 2', 1500);

        \Cart::instance('user_1')->add($book1);
        \Cart::instance('user_2')->add($book2);

        $this->assertEquals(123, \Cart::instance('user_1')->items()->first()->identifier);
        $this->assertEquals(456, \Cart::instance('user_2')->items()->first()->identifier);
    }

    public function testHasItemInCart()
    {
        $book = new Book(123, 'The Book', 1000);

        \Cart::add($book);

        $this->assertTrue(\Cart::has(123));
        $this->assertFalse(\Cart::has(456));
    }

    public function testFindItemInCart()
    {
        $book1 = new Book(123, 'The Book 1', 1000);
        $book2 = new Book(456, 'The Book 2', 1500);

        \Cart::add($book1);
        \Cart::add($book2);

        $this->assertEquals(CartItem::class, get_class(\Cart::find(123)));
    }

    public function testFindNotExistsItemInCart()
    {
        $book1 = new Book(123, 'The Book 1', 1000);
        $book2 = new Book(456, 'The Book 2', 1500);

        \Cart::add($book1);
        \Cart::add($book2);

        $this->expectException(ItemNotFoundException::class);

        \Cart::find(999);
    }

    public function testSearchItemInCart()
    {
        $book1 = new Book(123, 'The Book 1', 1000);
        $book2 = new Book(456, 'The Book 2', 1500);

        \Cart::add($book1);
        \Cart::add($book2);

        $items = \Cart::search(function (CartItem $cartItem) {
            return $cartItem->price <= 1000;
        });

        $this->assertEquals(1, $items->count());
        $this->assertEquals(123, $items->first()->identifier);
    }

    public function testEmptyCart()
    {
        $this->assertEquals(Cart::class, get_class(\Cart::get()));
        $this->assertEquals(Collection::class, get_class(\Cart::items()));
        $this->assertEquals(null, \Cart::items()->first());
        $this->assertEquals(true, \Cart::isEmpty());
        $this->assertEquals(0, \Cart::count());
        $this->assertEquals(0, \Cart::quantity());
        $this->assertEquals(0, \Cart::subtotal());
    }

    public function testAddItemToCart()
    {
        $book = new Book(123, 'The Book', 1000);

        \Cart::add($book);

        $this->assertEquals(Cart::class, get_class(\Cart::get()));
        $this->assertEquals(Collection::class, get_class(\Cart::items()));
        $this->assertEquals(CartItem::class, get_class(\Cart::items()->first()));
        $this->assertEquals(123, \Cart::items()->first()->identifier);
        $this->assertEquals(1, \Cart::items()->first()->quantity);
        $this->assertEquals(1000, \Cart::items()->first()->price);
        $this->assertEquals(false, \Cart::isEmpty());
        $this->assertEquals(1, \Cart::count());
        $this->assertEquals(1, \Cart::quantity());
        $this->assertEquals(1000, \Cart::subtotal());
    }

    public function testAddItemToCartWithArray()
    {
        $book = new Book(123, 'The Book', 1000);

        \Cart::add($book, [
            'quantity' => 2,
            'price' => 900,
            'attributes' => [
                'foo' => 'bar'
            ],
        ]);

        $this->assertEquals(123, \Cart::items()->first()->identifier);
        $this->assertEquals(2, \Cart::items()->first()->quantity);
        $this->assertEquals(900, \Cart::items()->first()->price);
        $this->assertEquals(false, \Cart::isEmpty());
        $this->assertEquals(1, \Cart::count());
        $this->assertEquals(2, \Cart::quantity());
        $this->assertEquals(1800, \Cart::subtotal());
    }

    public function testAddItemToCartMultipleTime()
    {
        $book = new Book(123, 'The Book', 1000);

        \Cart::add($book);
        \Cart::add($book);
        \Cart::add($book);

        $this->assertEquals(3, \Cart::items()->first()->quantity);
        $this->assertEquals(1000, \Cart::items()->first()->price);
        $this->assertEquals(false, \Cart::isEmpty());
        $this->assertEquals(1, \Cart::count());
        $this->assertEquals(3, \Cart::quantity());
        $this->assertEquals(3000, \Cart::subtotal());
    }

    public function testAddItemToCartWithCustomize()
    {
        $book = new Book(123, 'The Book', 1000);

        \Cart::add($book, 2, 900, ['foo' => 'bar']);

        $this->assertEquals(2, \Cart::items()->first()->quantity);
        $this->assertEquals(900, \Cart::items()->first()->price);
        $this->assertEquals(['foo' => 'bar'], \Cart::items()->first()->attributes);
        $this->assertEquals(false, \Cart::isEmpty());
        $this->assertEquals(1, \Cart::count());
        $this->assertEquals(2, \Cart::quantity());
        $this->assertEquals(1800, \Cart::subtotal());

        \Cart::add($book);

        $this->assertEquals(3, \Cart::items()->first()->quantity);
        $this->assertEquals(900, \Cart::items()->first()->price);
        $this->assertEquals(false, \Cart::isEmpty());
        $this->assertEquals(1, \Cart::count());
        $this->assertEquals(3, \Cart::quantity());
        $this->assertEquals(2700, \Cart::subtotal());
    }

    public function testAddItemToCartWithZeroQuantity()
    {
        $book = new Book(123, 'The Book', 1000);

        $this->expectException(\InvalidArgumentException::class);

        \Cart::add($book, 0);
    }

    public function testAddMultipleItemsToCart()
    {
        $book1 = new Book(123, 'The Book 1', 1000);
        $book2 = new Book(456, 'The Book 2', 1500);

        \Cart::add($book1);
        \Cart::add($book1, 2);
        \Cart::add($book2);

        $this->assertEquals(false, \Cart::isEmpty());
        $this->assertEquals(2, \Cart::count());
        $this->assertEquals(4, \Cart::quantity());
        $this->assertEquals(4500, \Cart::subtotal());
    }

    public function testUpdateItemFromCart()
    {
        $book = new Book(123, 'The Book', 1000);

        \Cart::add($book);

        \Cart::update(123, 3);

        $this->assertEquals(3, \Cart::items()->first()->quantity);
        $this->assertEquals(false, \Cart::isEmpty());
        $this->assertEquals(1, \Cart::count());
        $this->assertEquals(3, \Cart::quantity());
        $this->assertEquals(3000, \Cart::subtotal());
    }

    public function testUpdateItemFromCartWithQuantityZero()
    {
        $book = new Book(123, 'The Book', 1000);

        \Cart::add($book, 2);

        \Cart::update(123, 0);

        $this->assertEquals(null, \Cart::items()->first());
        $this->assertEquals(true, \Cart::isEmpty());
        $this->assertEquals(0, \Cart::count());
        $this->assertEquals(0, \Cart::quantity());
        $this->assertEquals(0, \Cart::subtotal());
    }

    public function testUpdateItemFromCartWithParameters()
    {
        $book = new Book(123, 'The Book', 1000);

        \Cart::add($book);

        $this->assertEquals(1, \Cart::items()->first()->quantity);
        $this->assertEquals([], \Cart::items()->first()->attributes);
        $this->assertEquals(false, \Cart::isEmpty());
        $this->assertEquals(1, \Cart::count());
        $this->assertEquals(1, \Cart::quantity());
        $this->assertEquals(1000, \Cart::subtotal());

        \Cart::update(123, [
            'quantity' => 3,
            'price' => 900,
            'attributes' => [
                'foo' => 'foo'
            ]
        ]);

        $this->assertEquals(3, \Cart::items()->first()->quantity);
        $this->assertEquals(['foo' => 'foo'], \Cart::items()->first()->attributes);
        $this->assertEquals(false, \Cart::isEmpty());
        $this->assertEquals(1, \Cart::count());
        $this->assertEquals(3, \Cart::quantity());
        $this->assertEquals(2700, \Cart::subtotal());
    }

    public function testRemoveItemFromCart()
    {
        $book = new Book(123, 'The Book', 1000);

        \Cart::add($book);

        \Cart::remove(123);

        $this->assertEquals(null, \Cart::items()->first());
        $this->assertEquals(true, \Cart::isEmpty());
        $this->assertEquals(0, \Cart::count());
        $this->assertEquals(0, \Cart::quantity());
        $this->assertEquals(0, \Cart::subtotal());
    }

    public function testDestroyCart()
    {
        $book = new Book(123, 'The Book', 1000);

        \Cart::add($book);

        \Cart::destroy();

        $this->assertEquals(null, \Cart::items()->first());
        $this->assertEquals(true, \Cart::isEmpty());
        $this->assertEquals(0, \Cart::count());
        $this->assertEquals(0, \Cart::quantity());
        $this->assertEquals(0, \Cart::subtotal());
    }

    public function testCalculateSubtotalItemFromCart()
    {
        $book = new Book(123, 'The Book', 1000);

        \Cart::add($book);

        $this->assertEquals(1000, \Cart::items()->first()->getSubtotal());

        \Cart::update(123, 2);

        $this->assertEquals(2000, \Cart::items()->first()->getSubtotal());
    }

    public function testCartCondition()
    {
        \Cart::addCondition('10% off #1', new TenPercentOffIfSubtotalOver500Baht);

        $this->assertEquals(Collection::class, get_class(\Cart::conditions()));
        $this->assertEquals(true, \Cart::conditions()->has('10% off #1'));
        $this->assertEquals(CartCondition::class, get_class(\Cart::conditions()->first()));
    }

    public function testCartConditionModifyTotal()
    {
        $book = new Book(123, 'The Book', 1000);

        \Cart::add($book);

        \Cart::addCondition('10% off #1', new TenPercentOffIfSubtotalOver500Baht);

        $this->assertEquals(1000, \Cart::subtotal());
        $this->assertEquals(900, \Cart::total());

        \Cart::addCondition('10% off #2', new TenPercentOffIfSubtotalOver500Baht);

        $this->assertEquals(1000, \Cart::subtotal());
        $this->assertEquals(800, \Cart::total());

        \Cart::addCondition('50 Baht Voucher', new FiftyBahtVoucher);

        $this->assertEquals(1000, \Cart::subtotal());
        $this->assertEquals(750, \Cart::total());
    }

    public function testCartConditionNotAllow()
    {
        $book = new Book(123, 'The Book', 1000);

        \Cart::add($book);

        $this->expectException(ConditionIsNotAllowedToAddException::class);

        \Cart::addCondition('50 Baht Voucher #1', new FiftyBahtVoucher);
        \Cart::addCondition('50 Baht Voucher #2', new FiftyBahtVoucher);
    }

    public function testCartConditionModifyItems()
    {
        $book = new Book(123, 'The Book', 1000);
        $anotherBook = new Book(456, 'Another Book', 300);

        \Cart::add($book);

        \Cart::addCondition('Free Another Book', new FreeAnotherBookIfTheBookInCart($anotherBook));

        $this->assertEquals(true, \Cart::has('conditionItem_456'));
        $this->assertEquals(2, \Cart::count());
        $this->assertEquals(2, \Cart::quantity());
        $this->assertEquals(1000, \Cart::subtotal());
        $this->assertEquals(1000, \Cart::total());
    }

    public function testRemoveCartConditionModifyTotal()
    {
        $book = new Book(123, 'The Book', 1000);

        \Cart::add($book);

        \Cart::addCondition('10% off', new TenPercentOffIfSubtotalOver500Baht);

        $this->assertEquals(Collection::class, get_class(\Cart::conditions()));
        $this->assertEquals(true, \Cart::conditions()->has('10% off'));
        $this->assertEquals(CartCondition::class, get_class(\Cart::conditions()->first()));
        $this->assertEquals(1000, \Cart::subtotal());
        $this->assertEquals(900, \Cart::total());

        \Cart::removeCondition('10% off');

        $this->assertEquals(Collection::class, get_class(\Cart::conditions()));
        $this->assertEquals(false, \Cart::conditions()->has('10% off'));
        $this->assertEquals(null, \Cart::conditions()->first());
        $this->assertEquals(1000, \Cart::subtotal());
        $this->assertEquals(1000, \Cart::total());
    }

    public function testRemoveCartConditionModifyItems()
    {
        $book = new Book(123, 'The Book', 1000);
        $anotherBook = new Book(456, 'Another Book', 300);

        \Cart::add($book);

        \Cart::addCondition('Free Another Book', new FreeAnotherBookIfTheBookInCart($anotherBook));

        $this->assertEquals(true, \Cart::has('conditionItem_456'));
        $this->assertEquals(2, \Cart::count());
        $this->assertEquals(2, \Cart::quantity());
        $this->assertEquals(1000, \Cart::subtotal());
        $this->assertEquals(1000, \Cart::total());

        \Cart::removeCondition('Free Another Book');

        $this->assertEquals(false, \Cart::has('conditionItem_456'));
        $this->assertEquals(1, \Cart::count());
        $this->assertEquals(1, \Cart::quantity());
        $this->assertEquals(1000, \Cart::subtotal());
        $this->assertEquals(1000, \Cart::total());
    }

    public function testRemoveItemThatAffectCartConditionModifyItems()
    {
        $book = new Book(123, 'The Book', 1000);
        $anotherBook = new Book(456, 'Another Book', 300);

        \Cart::add($book);

        \Cart::addCondition('Free Another Book', new FreeAnotherBookIfTheBookInCart($anotherBook));

        $this->assertEquals(true, \Cart::has('conditionItem_456'));
        $this->assertEquals(2, \Cart::count());
        $this->assertEquals(2, \Cart::quantity());
        $this->assertEquals(1000, \Cart::subtotal());
        $this->assertEquals(1000, \Cart::total());

        \Cart::remove(123);

        $this->assertEquals(false, \Cart::has('conditionItem_456'));
        $this->assertEquals(0, \Cart::count());
        $this->assertEquals(0, \Cart::quantity());
        $this->assertEquals(0, \Cart::subtotal());
        $this->assertEquals(0, \Cart::total());
    }

    public function testDestroyCartWithConditions()
    {
        $book = new Book(123, 'The Book', 1000);

        \Cart::add($book);

        \Cart::addCondition('10% off #1', new TenPercentOffIfSubtotalOver500Baht);
        \Cart::addCondition('10% off #2', new TenPercentOffIfSubtotalOver500Baht);

        \Cart::destroy();

        $this->assertEquals(null, \Cart::conditions()->first());
    }

    public function testUpdateCartAffectCartCondition()
    {
        $book = new Book(123, 'The Book', 1000);

        \Cart::add($book);

        \Cart::addCondition('10% off', new TenPercentOffIfSubtotalOver500Baht);

        $this->assertEquals(1000, \Cart::subtotal());
        $this->assertEquals(900, \Cart::total());

        \Cart::update(123, [
            'price' => 400
        ]);

        $this->assertEquals(400, \Cart::subtotal());
        $this->assertEquals(400, \Cart::total());
    }

    public function testUnreliableCondition()
    {
        $book = new Book(123, 'The Book', 1000);

        \Cart::add($book);

        \Cart::addCondition('Black Friday Sale', new UnreliablePromotion);

        $this->assertEquals(1000, \Cart::subtotal());
        $this->assertEquals(500, \Cart::total());

        \Cart::refreshConditions(); // after the event

        $this->assertEquals(1000, \Cart::subtotal());
        $this->assertEquals(1000, \Cart::total());

        \Cart::refreshConditions(); // back to the event

        $this->assertEquals(1000, \Cart::subtotal());
        $this->assertEquals(500, \Cart::total());
    }

    public function testHasCondition()
    {
        \Cart::addCondition('10% off', new TenPercentOffIfSubtotalOver500Baht);

        $this->assertEquals(true, \Cart::hasCondition('10% off'));
        $this->assertEquals(false, \Cart::hasCondition('100% off'));
    }

    public function testFindCondition()
    {
        \Cart::addCondition('10% off', new TenPercentOffIfSubtotalOver500Baht);
        \Cart::addCondition('50 Baht Voucher', new FiftyBahtVoucher);

        $this->assertEquals(CartCondition::class, get_class(\Cart::findCondition('10% off')));
        $this->assertEquals(TenPercentOffIfSubtotalOver500Baht::class, get_class(\Cart::findCondition('10% off')->getCondition()));
    }

    public function testFindNotExistsCondition()
    {
        $this->expectException(ConditionNotFoundException::class);

        $this->assertEquals(TenPercentOffIfSubtotalOver500Baht::class, \Cart::findCondition('10% off'));
    }
}
