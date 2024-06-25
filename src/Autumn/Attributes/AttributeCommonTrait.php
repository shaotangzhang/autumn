<?php
/**
 * Autumn PHP Framework
 *
 * Date:        8/06/2024
 */

namespace Autumn\Attributes;

trait AttributeCommonTrait
{
    /**
     * @param \ReflectionClass|\ReflectionProperty|\ReflectionMethod|\ReflectionFunction|\ReflectionParameter $reflection
     * @return static[]
     */
    public static function ofReflection(
        \ReflectionClass|\ReflectionProperty|\ReflectionMethod|\ReflectionFunction|\ReflectionParameter $reflection
    ): iterable
    {
        foreach ($reflection->getAttributes(static::class) as $attribute) {
            yield $attribute->newInstance();
        }
    }
}