<?php
/**
 * Autumn PHP Framework
 *
 * Date:        13/02/2024
 */

namespace Autumn\Events;

interface EventHandlerInterface
{
    public function handle(EventInterface $event): mixed;
}