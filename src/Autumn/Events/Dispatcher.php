<?php
/**
 * Autumn PHP Framework
 *
 * Date:        13/02/2024
 */

namespace Autumn\Events;

use Autumn\System\Application;
use Autumn\System\ClassFactory;

class Dispatcher implements ListenerProviderInterface
{
    private array $listeners = [];

    public function getListenersForEvent(object $event): iterable
    {
        $class = $event::class;
        if (!isset($this->listeners[$class])) {
            $this->listeners[$class] = $this->loadListenersFromConfig($class);
        }

        foreach ($this->listeners[$class] as $listener) {
            if (is_callable($listener)) {
                yield $listener;
            }

            if (is_string($listener)) {
                if (is_subclass_of($listener, EventHandlerInterface::class)) {
                    if ($handler = ClassFactory::make($listener)) {
                        yield [$handler, 'handle'];
                    }
                } elseif (is_callable($listener)) {
                    yield $listener;
                }
            } elseif ($listener instanceof EventHandlerInterface) {
                yield [$listener, 'handle'];
            }
        }
    }

    public function addListener(string|EventInterface $event, callable $listener): void
    {
        if (is_string($event)) {
            if (!is_subclass_of($event, EventInterface::class)) {
                throw new \InvalidArgumentException(sprintf(
                    'The first argument must be the class of Event, "%s" given.', $event
                ));
            }
        } else {
            $event = $event::class;
        }

        if (!isset($this->listeners[$event]) || !in_array($listener, $this->listeners[$event], true)) {
            $this->listeners[$event][] = $listener;
        }
    }

    /**
     * Load listeners from configuration.
     *
     * @param string $eventName The name of the event.
     * @return iterable The array of listeners loaded from configuration.
     */
    public function loadListenersFromConfig(string $eventName): iterable
    {
        $list = Application::context()->forEventListeners($eventName);
        if (is_iterable($list)) {
            return $list;
        }

        if ($list instanceof \Closure) {
            return [$list];
        }

        return [];
    }

    public function dispatch(object $event): object
    {
        $listeners = $this->getListenersForEvent($event);

        if ($event instanceof \Psr\EventDispatcher\StoppableEventInterface) {
            foreach ($listeners as $listener) {
                if (!$event->isPropagationStopped()) {
                    return $event;
                }

                $event = call_user_func($listener, $event);
            }
        } else {
            foreach ($listeners as $listener) {
                $event = call_user_func($listener, $event);
            }
        }

        return $event;
    }
}