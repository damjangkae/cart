<?php

namespace Damjangkae\Cart;

use \Damjangkae\Cart\Console\Commands\Cart;
use Illuminate\Support\ServiceProvider;
use Damjangkae\Cart\Providers\EventServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Cart::class
            ]);
        }

        $this->mergeConfigFrom(__DIR__ . '/../config/cart.php', 'cart');

        $this->publishes([__DIR__ . '/../config/cart.php' => config_path('cart.php')], 'config');

        $timestamp = date('Y_m_d_His', time());
        $this->publishes([
            __DIR__ . '/../database/migrations/0000_00_00_000000_create_carts_table.php' => database_path('migrations/' . $timestamp . '_create_carts_table.php'),
        ], 'migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(EventServiceProvider::class);

        $this->app->bind(CartManagerInterface::class, CartManager::class);
    }
}