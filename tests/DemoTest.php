<?php

namespace Damjangkae\Cart\Tests;

use Damjangkae\Cart\CartServiceProvider;
use Damjangkae\Cart\Tests\Mocks\Book;
use Damjangkae\Cart\Tests\Mocks\TenPercentOffIfSubtotalOver500Baht;
use Orchestra\Testbench\TestCase;

class DemoTest extends TestCase
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

    public function testDemo()
    {
        $book1 = new Book(123, 'The Book 1', 1000);

        \Cart::add($book1);

        $this->assertEquals(1000, \Cart::subtotal());

        \Cart::addCondition('10% off #1', new TenPercentOffIfSubtotalOver500Baht());

        $this->assertEquals(900, \Cart::total());

    }
}
