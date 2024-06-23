<?php
/**
 * Autumn PHP Framework
 *
 * Date:        13/02/2024
 */

namespace Autumn\Events;

trait EventHandlerTrait
{
    public function handle(EventInterface $event): mixed
    {
        $this->process($event);
        return $event;
    }

    public function process(EventInterface $event): void
    {

    }
}