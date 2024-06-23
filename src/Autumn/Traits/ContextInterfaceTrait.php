<?php
/**
 * Autumn PHP Framework
 *
 * Date:        23/06/2024
 */

namespace Autumn\Traits;

use Autumn\Attributes\Transient;

trait ContextInterfaceTrait
{
    #[Transient]
    private static array $instances = [];

    public static function context(): static
    {
        return self::$instances[static::class] ??= static::createDefaultInstance();
    }

    protected static function createDefaultInstance(): static
    {
        return new static;
    }
}