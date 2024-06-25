<?php
/**
 * Autumn PHP Framework
 *
 * Date:        25/06/2024
 */

namespace Autumn\Database\Events;

use Autumn\Database\Interfaces\EntityInterface;
use Autumn\Events\Event;
use Autumn\Events\EventHandlerInterface;
use Autumn\Events\EventInterface;
use Autumn\Interfaces\ContextInterface;
use Autumn\Traits\ContextInterfaceTrait;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Class EntityEventDispatcher
 *
 * This class manages event handling for entity-related events.
 * It implements EventHandlerInterface for handling events and ContextInterface for managing context.
 */
class EntityEventDispatcher implements EventHandlerInterface, ContextInterface
{
    use ContextInterfaceTrait;

    /**
     * @var array<string, array<string, array<string|callable|EventHandlerInterface>>>
     */
    private static array $handlers = [];

    /**
     * The list of events registered with this dispatcher.
     */
    public const REGISTERED_EVENTS = [
        'creating' => EntityCreatingEvent::class,
        'created' => EntityCreatedEvent::class,
        'updating' => EntityUpdatingEvent::class,
        'updated' => EntityUpdatedEvent::class,
        'deleting' => EntityDeletingEvent::class,
        'deleted' => EntityDeletedEvent::class,
    ];

    /**
     * Registers event listeners on the default instance when created.
     *
     * @return static The created default instance.
     */
    protected static function createDefaultInstance(): static
    {
        $instance = new static;

        foreach (static::REGISTERED_EVENTS as $event) {
            Event::listen($event, $instance);
        }

        return $instance;
    }

    /**
     * Registers a class or instance to process the entity events
     *
     * @param string $entity
     * @param string|EntityEventHandlerInterface $handler
     * @return void
     */
    public static function hook(string $entity, string|EntityEventHandlerInterface $handler): void
    {
        foreach (static::REGISTERED_EVENTS as $action => $event) {
            if (method_exists($handler, $action)) {
                if (is_string($handler)) {
                    static::listen($event, $entity, fn($e) => app($handler, true)->$action($e));
                } else {
                    static::listen($event, $entity, [$handler, $action]);
                }
            }
        }
    }

    /**
     * Registers a listener for a specific event associated with a table name.
     *
     * @param string|EventInterface $event The event class or event instance to listen for.
     * @param string $entity The name of the entity or its table associated with the event.
     * @param string|callable|EventHandlerInterface $handler The handler for the event.
     * @throws \InvalidArgumentException If the table name is empty or the event type is invalid.
     */
    public static function listen(string|EventInterface $event, string $entity, string|callable|EventHandlerInterface $handler): void
    {
        $eventName = is_string($event) ? $event : $event::class;

        if (empty($tableName = trim($entity))) {
            throw new \InvalidArgumentException(sprintf(
                'Table name is required for a listener of event `%s`.',
                $eventName
            ));
        } elseif (is_subclass_of($tableName, EntityInterface::class)) {
            $tableName = $tableName::entity_name();
        } else {
            $tableName = strtolower($tableName);
        }

        if (!is_subclass_of($event, EntityEventInterface::class)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid event type `%s` to listen.',
                is_string($event) ? $event : $event::class
            ));
        }

        if (isset(self::$handlers[$tableName][$eventName])) {
            if (in_array($handler, self::$handlers[$tableName][$eventName], true)) {
                return;
            }
        }

        // Ensure handlers array exists for the table and event
        self::$handlers[$tableName][$eventName][] = $handler;
    }

    /**
     * Handles an event by invoking all registered handlers.
     *
     * @param EventInterface $event The event instance to handle.
     */
    public function handle(EventInterface $event): void
    {
        if ($event instanceof EntityEventInterface) {
            $tableName = $event->getEntity()::entity_name();
            $handlers = static::$handlers[$tableName][$event::class] ?? [];

            foreach ($handlers as $handler) {
                $this->callHandler($handler, $event);
                if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                    break;
                }
            }
        }
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
     * Invokes a handler implemented as a class.
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
