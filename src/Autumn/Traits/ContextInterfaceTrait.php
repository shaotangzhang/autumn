<?php
/**
 * Autumn PHP Framework
 *
 * Date:        20/06/2024
 */

namespace Autumn\Traits;

use Autumn\Interfaces\ContextInterface;
use Autumn\System\ServiceContainer\DecoratorProxy;

trait ContextInterfaceTrait // implements ContextInterface
{
    /**
     * @var array<string, self>
     */
    private static array $instances = [];

    /**
     * Get the current context instance of the service.
     *
     * This method returns an instance of the service class, ensuring it is resolved from the application
     * container as a singleton.
     *
     * @return static An instance of the service class.
     */
    public static function context(): static
    {
        if (isset(static::$instances[static::class])) {
            return static::$instances[static::class];
        }

        if ($class = DecoratorProxy::createProxyClass(static::class)) {
            if (is_subclass_of($class, self::class)) {
                return static::$instances[static::class] = $class::createDefaultInstance();
            }
        }

        return static::$instances[static::class] = static::createDefaultInstance();
    }

    /**
     * Create the default instance
     *
     * @return static
     */
    protected static function createDefaultInstance(): static
    {
        return new static;
    }
}