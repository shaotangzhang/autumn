<?php
/**
 * Autumn PHP Framework
 *
 * Date:        25/06/2024
 */

namespace Autumn\Attributes;

trait PropertyAttributeTrait
{
    private ?\ReflectionProperty $property = null;

    /**
     * @param \ReflectionProperty $reflection
     * @return static[]
     */
    public static function forReflection(\ReflectionProperty $reflection): iterable
    {
        foreach ($reflection->getAttributes(static::class) as $attribute) {
            $instance = $attribute->newInstance();
            $instance->property = $reflection;
            yield $instance;
        }
    }

    /**
     * @return \ReflectionProperty|null
     */
    public function getProperty(): ?\ReflectionProperty
    {
        return $this->property;
    }

    /**
     * @param \ReflectionProperty|null $property
     */
    public function setProperty(?\ReflectionProperty $property): void
    {
        $this->property = $property;
    }

    public function withProperty(?\ReflectionProperty $property): static
    {
        if ($property === $this->property) {
            return $this;
        }

        $clone = clone $this;
        $clone->property = $property;
        return $clone;
    }
}