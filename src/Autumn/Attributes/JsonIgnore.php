<?php

namespace Autumn\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JsonIgnore
{
    use HasOfReflection;

    public const IGNORE_ANY = 'any';
    public const IGNORE_NULL = 'null';
    public const IGNORE_EMPTY = 'empty';
    public const IGNORE_BLANK = 'blank';
    public const IGNORE_ZERO = 'zero';
    public const IGNORE_NONE = 'none';

    public function __construct(public readonly string $ignore = self::IGNORE_NULL)
    {
    }

    public function ignorable(mixed $value, object $instance = null, mixed $defaultValue = null): mixed
    {
        if ($this->ignore === static::IGNORE_ANY) {
            return $defaultValue;
        }

        if ($instance && ($value instanceof \ReflectionProperty)) {
            $value = $value->getValue($instance);
        }

        return match ($this->ignore) {
            static::IGNORE_EMPTY => $value ?: $defaultValue,
            static::IGNORE_BLANK => ($value === '') ? $defaultValue : $value,
            static::IGNORE_ZERO => ($value === 0 || $value === 0.0) ? $defaultValue : $value,
            static::IGNORE_NONE => (is_array($value) && !count($value)) ? $defaultValue : $value,
            default => $value,
        };
    }
}