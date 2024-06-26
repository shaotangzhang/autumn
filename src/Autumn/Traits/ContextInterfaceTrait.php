<?php
/**
 * Autumn PHP Framework
 *
 * Date:        20/06/2024
 */

namespace Autumn\Traits;

use Autumn\Attributes\Transient;

trait ContextInterfaceTrait // implements ContextInterface
{
    /**
     * @var array<string, static>
     */
    #[Transient]
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
        if (isset(self::$instances[static::class])) {
            return self::$instances[static::class];
        }

//        if ($class = DecoratorProxy::createProxyClass(static::class)) {
//            if (is_subclass_of($class, self::class)) {
//                return static::$instances[static::class] = $class::createDefaultInstance();
//            }
//        }

        return self::$instances[static::class] = static::createDefaultInstance();
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