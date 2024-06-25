<?php
/**
 * Autumn PHP Framework
 *
 * Date:        13/02/2024
 */

namespace Autumn\Events;

interface StoppableEventInterface extends EventInterface, \Psr\EventDispatcher\StoppableEventInterface
{    public function stopPropagation(): void;
}