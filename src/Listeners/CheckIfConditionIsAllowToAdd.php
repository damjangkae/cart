<?php

namespace Damjangkae\Cart\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Damjangkae\Cart\Events\ConditionMustBeProvided;
use Damjangkae\Cart\Exceptions\ConditionIsNotAllowedToAddException;

class CheckIfConditionIsAllowToAdd
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  AddedItemToCart  $event
     * @return void
     */
    public function handle(ConditionMustBeProvided $event)
    {
        if (!$event->getCartCondition()->getCondition()->allow($event->getCart()))
        {
            throw new ConditionIsNotAllowedToAddException('Condition ' . get_class($event->getCartCondition()->getCondition()) . ' is not allowed to add.');
        }
    }
}
