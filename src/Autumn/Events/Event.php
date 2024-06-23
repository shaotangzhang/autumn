<?php
/**
 * Autumn PHP Framework
 *
 * Date:        13/02/2024
 */

namespace Autumn\Events;

use Autumn\App;
use Autumn\System\ClassFactory;

class Event
{
    private static ?ListenerProviderInterface $dispatcher = null;

    public static function fire(string $event, object $sender = null, mixed ...$args): bool
    {
        return true;
    }

    private static function dispatcher(): ?ListenerProviderInterface
    {
        return self::$dispatcher ??= App::context()->getServiceContainer()->make(ListenerProviderInterface::class) ?? new Dispatcher();
    }

    public static function dispatch(EventInterface $event): bool
    {
        $event = self::dispatcher()->dispatch($event);
        if ($event instanceof \Psr\EventDispatcher\StoppableEventInterface) {
            return !$event->isPropagationStopped();
        }
        return true;
    }

    public static function listen(string|EventInterface $event, string|callable|EventHandlerInterface $handler): void
    {
        if (is_string($handler)) {
            if (!is_callable($handler)) {
                if (!is_subclass_of($handler, EventHandlerInterface::class)) {
                    throw new \InvalidArgumentException(sprintf(
                        'The handler must be the class of EventHandlerInterface or a callable, "%s" given.',
                        $handler
                    ));
                }

                $handler = function (EventInterface $event) use ($handler) {
                    return App::context()->getServiceContainer()->make($handler)?->handle($event);
                };
            }
        } elseif ($handler instanceof EventHandlerInterface) {
            $handler = function (EventInterface $event) use ($handler) {
                return $handler->handle($event);
            };
        }

        self::dispatcher()->addListener($event, $handler);
    }
}