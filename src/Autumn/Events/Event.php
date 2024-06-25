<?php
/**
 * Autumn PHP Framework
 *
 * Date: 13/02/2024
 */

namespace Autumn\Events;

class Event implements EventInterface
{
    private static ?ListenerProviderInterface $dispatcher = null;

    /**
     * @param string $name The name of the event.
     * @param object|null $sender The object that triggered the event, or null if not specified.
     * @param array $args Additional arguments passed with the event.
     */
    public function __construct(
        private readonly string $name,
        private readonly ?object $sender = null,
        private readonly array $args = []
    ) {
    }

    /**
     * Fires an event.
     *
     * @param string $event The name of the event to fire.
     * @param object|null $sender The object that triggered the event, or null if not specified.
     * @param mixed ...$args Additional arguments to pass with the event.
     */
    public static function fire(string $event, object $sender = null, mixed ...$args): void
    {
        $object = new static($event, $sender, $args);
        static::dispatcher()->dispatch($object);
    }

    /**
     * Retrieves the event dispatcher instance.
     *
     * @return ListenerProviderInterface The event dispatcher instance.
     */
    private static function dispatcher(): ListenerProviderInterface
    {
        return self::$dispatcher ??= new Dispatcher;
    }

    /**
     * Registers a listener for a specific event.
     *
     * @param string|EventInterface $event The class name of the event or an event object.
     * @param string|callable|EventHandlerInterface $handler The listener or handler to register.
     */
    public static function listen(string|EventInterface $event, string|callable|EventHandlerInterface $handler): void
    {
        self::dispatcher()->addListener($event, $handler);
    }

    /**
     * Dispatches an event and returns whether propagation was stopped.
     *
     * @param EventInterface $event The event object to dispatch.
     * @return bool True if propagation was not stopped, false otherwise.
     */
    public static function dispatch(EventInterface $event): bool
    {
        $event = self::dispatcher()->dispatch($event);
        if ($event instanceof \Psr\EventDispatcher\StoppableEventInterface) {
            return !$event->isPropagationStopped();
        }
        return true;
    }

    /**
     * Retrieves the name of the event.
     *
     * @return string The event name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Retrieves the additional arguments passed with the event.
     *
     * @return array The event arguments.
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * Retrieves the object that triggered the event, or null if not specified.
     *
     * @return object|null The event sender object.
     */
    public function getSender(): ?object
    {
        return $this->sender;
    }
}
