<?php
/**
 * Autumn PHP Framework
 *
 * Date:        13/02/2024
 */

namespace Autumn\Events;

use Psr\EventDispatcher\EventDispatcherInterface;

interface ListenerProviderInterface extends \Psr\EventDispatcher\ListenerProviderInterface, EventDispatcherInterface
{
    public function addListener(string|EventInterface $event, callable $listener): void;

}