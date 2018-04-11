<?php

namespace Damjangkae\Cart\Providers;

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
use Damjangkae\Cart\Events\UpdatingItem;
use Damjangkae\Cart\Listeners\CheckIfConditionIsAllowToAdd;
use Damjangkae\Cart\Listeners\PerformConditions;
use Damjangkae\Cart\Listeners\RemoveItemFromCartIfQuantityIsZero;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Damjangkae\Cart\Events\UpdatedItem;
use Damjangkae\Cart\Listeners\ThrowExceptionIfTryToAddZeroQuantity;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        AddingItem::class => [
            ThrowExceptionIfTryToAddZeroQuantity::class
        ],
        AddedItem::class => [
            PerformConditions::class
        ],
        UpdatingItem::class => [

        ],
        UpdatedItem::class => [
            RemoveItemFromCartIfQuantityIsZero::class,
            PerformConditions::class
        ],
        RemovingItem::class => [

        ],
        RemovedItem::class => [
            PerformConditions::class
        ],
        AddingCondition::class => [
            CheckIfConditionIsAllowToAdd::class
        ],
        AddedCondition::class => [
            PerformConditions::class
        ],
        RemovingCondition::class => [

        ],
        RemovedCondition::class => [
            PerformConditions::class
        ],
        RefreshedConditions::class => [
            PerformConditions::class
        ],
        DestroyingCart::class => [

        ],
        DestroyedCart::class => [

        ],
        StoringCart::class => [

        ],
        StoredCart::class => [

        ],
        RestoringCart::class => [

        ],
        RestoredCart::class => [

        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
