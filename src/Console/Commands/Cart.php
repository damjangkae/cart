<?php

namespace Damjangkae\Cart\Console\Commands;

use Illuminate\Console\Command;

class Cart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cart {action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cart commands';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        switch ($this->argument('action')) {
            case 'clear':
                $modelCart = config('cart.model.cart');
                $modelCart::truncate();
                break;
        }
    }
}
