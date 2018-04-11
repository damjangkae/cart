<?php

namespace Damjangkae\Cart\Events;

trait IdentifierProvidable
{
    public function getIdentifier()
    {
        return $this->identifier;
    }
}