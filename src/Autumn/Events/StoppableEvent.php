<?php
/**
 * Autumn PHP Framework
 *
 * Date:        13/02/2024
 */

namespace Autumn\Events;

class StoppableEvent implements StoppableEventInterface
{
    use StoppableEventTrait;

    public function __construct(array $context = null)
    {
        $this->context = $context ?? [];
    }
}