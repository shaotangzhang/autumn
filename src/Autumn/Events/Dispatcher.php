<?php

namespace Autumn\Events;

use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Class Dispatcher
 *
 * Event dispatcher that manages event listeners and dispatches events to registered handlers.
 */
class Dispatcher implements ListenerProviderInterface
{
    /**
     * @var array<string, array<callable|EventHandlerInterface|string>> Associative array storing listeners by event name.
     */
    private array $listeners = [];

    /**
     * Retrieves listeners for a specific event.
     *
     * @param object $event The event object for which listeners are requested.
     * @return iterable<callable|array{object, string}> Iterable collection of listeners.
     */
    public function getListenersForEvent(object $event): iterable
    {
        $eventName = (($event instanceof Event) ? $event->getName() : null) ?: get_class($event);

        foreach ($this->listeners[$eventName] ?? [] as $listener) {
            if (is_callable($listener)) {
                yield $listener;
            }

            if (is_string($listener)) {
                if (is_subclass_of($listener, EventHandlerInterface::class)) {
                    if ($handler = app($listener, true)) {
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

    /**
     * Adds a listener for a specific event.
     *
     * @param string|EventInterface $event The class name of the event or an event object.
     * @param string|callable|EventHandlerInterface $listener The listener to add.
     * @throws \InvalidArgumentException If the event class is invalid.
     */
    public function addListener(string|EventInterface $event, string|callable|EventHandlerInterface $listener): void
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
     * Dispatches an event to its registered listeners.
     *
     * @param object $event The event object to dispatch.
     * @return object The modified event object after all listeners have processed it.
     * @throws \RuntimeException If an error occurs while executing an event handler.
     */
    public function dispatch(object $event): object
    {
        if ($event instanceof EventInterface) {
            $eventName = $event instanceof Event ? $event->getName() : get_class($event);

            foreach ($this->listeners[$eventName] ?? [] as $handler) {
                $this->callHandler($handler, $event);
                if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                    break;
                }
            }
        }

        return $event;
    }

    /**
     * Calls a handler for an event.
     *
     * @param callable|EventHandlerInterface|string $handler The handler to call.
     * @param EventInterface $event The event instance to pass to the handler.
     * @throws \RuntimeException If an error occurs while executing the handler.
     * @throws \InvalidArgumentException If an invalid handler type is provided.
     */
    protected function callHandler(callable|EventHandlerInterface|string $handler, EventInterface $event): void
    {
        try {
            if (is_callable($handler)) {
                $handler($event);
            } elseif (is_string($handler)) {
                $this->invokeClassHandler($handler, $event);
            } elseif ($handler instanceof EventHandlerInterface) {
                $handler->handle($event);
            } else {
                throw new \InvalidArgumentException('Invalid handler type provided.');
            }
        } catch (\Throwable $e) {
            // Handle or log the exception as needed
            throw new \RuntimeException('Error executing event handler.', $e->getCode(), $e);
        }
    }

    /**
     * Invokes a class-based handler for an event.
     *
     * @param string $handlerClass The class name of the handler to invoke.
     * @param EventInterface $event The event instance to pass to the handler.
     * @throws \InvalidArgumentException If the handler class does not implement EventHandlerInterface.
     */
    protected function invokeClassHandler(string $handlerClass, EventInterface $event): void
    {
        if (is_subclass_of($handlerClass, EventHandlerInterface::class)) {
            $handler = app($handlerClass, true);
            $handler->handle($event);
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Handler class "%s" does not implement EventHandlerInterface.',
                $handlerClass
            ));
        }
    }
}
