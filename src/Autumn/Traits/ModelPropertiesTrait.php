<?php
/**
 * Autumn PHP Framework
 *
 * Date:        20/06/2024
 */

namespace Autumn\Traits;

use Autumn\Attributes\Transient;
use Autumn\System\Model;

trait ModelPropertiesTrait
{
    private static array $__properties__ = [];

    private static array $__getters__ = [];

    private static array $__setters__ = [];

    /**
     * @return array<string, \ReflectionProperty>
     */
    protected static function __properties__(): array
    {
        if (!isset(self::$__properties__[static::class])) {
            $transients = [];

            $reflection = new \ReflectionClass(static::class);
            foreach ($reflection->getProperties() as $property) {
                if ($property->getAttributes(Transient::class)) {
                    $transients[] = $property->getName();
                    continue;
                }

                self::$__properties__[static::class][$property->getName()] = $property;
            }

            if ($parentClass = get_parent_class(static::class)) {
                if (is_subclass_of($parentClass, Model::class)) {
                    foreach ($parentClass::__properties__() as $name => $property) {
                        if (!in_array($name, $transients)) {
                            self::$__properties__[static::class][$name] = $property;
                        }
                    }
                }
            }
        }

        return self::$__properties__[static::class];
    }

    protected static function __property_name__($property): string
    {
        $name = preg_replace('/[^a-zA-Z0-9]+/', ' ', $property);
        $name = str_replace(' ', '', ucwords($name));
        return lcfirst($name);
    }

    protected static function __getter__(string $property): ?string
    {
        $propertyName = static::__property_name__($property);

        if (!(static::$__getters__[static::class][$property] ??= false)) {
            if (method_exists(static::class, $func = 'get' . $propertyName)
                || method_exists(static::class, $func = 'is' . $propertyName)) {
                static::$__getters__[static::class][$property] = $func;
            }
        }

        return static::$__getters__[static::class][$property] ?: null;
    }

    protected static function __setter__(string $property): ?string
    {
        $propertyName = static::__property_name__($property);

        if (!(static::$__setters__[static::class][$property] ??= false)) {
            if (method_exists(static::class, $func = 'set' . $propertyName)) {
                static::$__setters__[static::class][$property] = $func;
            }
        }

        return static::$__setters__[static::class][$property] ?: null;
    }

    public function __get(string $name): mixed
    {
        if ($getter = static::__getter__($name)) {
            return $this->$getter();
        }

        return null;
    }

    public function __set(string $name, mixed $value): void
    {
        if ($setter = static::__setter__($name)) {
            $this->$setter($value);
        }
    }

    public function __isset(string $name): bool
    {
        return $this->__get($name) !== null;
    }

    public function __unset(string $name): void
    {
        $this->__set($name, null);
    }
}