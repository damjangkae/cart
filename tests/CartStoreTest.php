<?php

namespace Damjangkae\Cart\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Damjangkae\Cart\CartServiceProvider;
use Damjangkae\Cart\Exceptions\CartAlreadyStoredException;
use Damjangkae\Cart\Exceptions\CartNotFoundInStoreException;
use Damjangkae\Cart\Tests\Mocks\Book;
use Damjangkae\Cart\Tests\Mocks\TenPercentOffIfSubtotalOver500Baht;
use Orchestra\Testbench\TestCase;

class CartStoreTest extends TestCase
{
    use DatabaseTransactions;

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

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('cart.database.connection', 'testing');

        $app['config']->set('session.driver', 'array');

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->app->afterResolving('migrator', function ($migrator) {
            $migrator->path(realpath(__DIR__ . '/../database/migrations'));
        });

        $this->artisan('migrate', ['--database' => 'testing']);
    }

    public function testStoreAndRestore()
    {
        $book1 = new Book(123, 'The Book 1', 1000);

        \Cart::add($book1);
        \Cart::addCondition('10% off', new TenPercentOffIfSubtotalOver500Baht);

        \Cart::store('test_1');

        \Cart::restore('test_1');

        $this->assertEquals(123, \Cart::items()->first()->identifier);
        $this->assertEquals(TenPercentOffIfSubtotalOver500Baht::class, get_class(\Cart::conditions()->first()->getCondition()));
    }

    public function testStoreAndRestoreMultipleInstance()
    {
        $book1 = new Book(123, 'The Book 1', 1000);
        $book2 = new Book(456, 'The Book 2', 1500);

        \Cart::instance('user_1')->add($book1);
        \Cart::instance('user_2')->add($book2);

        \Cart::instance('user_1')->store('test_1');
        \Cart::instance('user_2')->store('test_2');

        \Cart::restore('test_1');
        \Cart::restore('test_2');

        $this->assertEquals(123, \Cart::instance('user_1')->items()->first()->identifier);
        $this->assertEquals(456, \Cart::instance('user_2')->items()->first()->identifier);
    }

    public function testDuplicateStore()
    {
        $book1 = new Book(123, 'The Book 1', 1000);
        $book2 = new Book(456, 'The Book 2', 1500);

        \Cart::instance('user_1')->add($book1);
        \Cart::instance('user_2')->add($book2);

        \Cart::instance('user_1')->store('test_1');

        $this->expectException(CartAlreadyStoredException::class);

        \Cart::instance('user_2')->store('test_1');
    }

    public function testRestoreNotExistsCartStore()
    {
        $this->expectException(CartNotFoundInStoreException::class);

        \Cart::restore('test_1');
    }

    public function testTruncate()
    {
        $book1 = new Book(123, 'The Book 1', 1000);

        \Cart::add($book1);
        \Cart::addCondition('10% off', new TenPercentOffIfSubtotalOver500Baht);

        \Cart::store('test_1');

        \Cart::truncate();

        $this->expectException(CartNotFoundInStoreException::class);

        \Cart::restore('test_1');
    }
}
