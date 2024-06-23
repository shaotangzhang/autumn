<?php

namespace Autumn\System\ClassFactory;

/**
 * Represents a PHP type and provides methods to check type properties.
 */
class Type
{
    public const STRING = 'string';
    public const INT = 'int';
    public const FLOAT = 'float';
    public const BOOL = 'bool';
    public const ARRAY = 'array';
    public const VOID = 'void';

    public const SCALAR_TYPES = ['string', 'int', 'float', 'bool'];
    public const COMPOUND_TYPES = ['array', 'iterable', 'object', 'callable', 'mixed'];
    public const SPECIAL_TYPES = ['void', 'null', 'never', 'false'];
    public const BUILD_IN_TYPES = [...self::SCALAR_TYPES, ...self::COMPOUND_TYPES];
    public const RETURN_TYPES = [...self::SPECIAL_TYPES, ...self::BUILD_IN_TYPES];

    private string $name;
    private array $types = [];

    private bool $nullable = false;

    /**
     * Constructs a Type object with the given type name.
     *
     * @param string $name The name of the type.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->nullable = $name === 'null';

        if (str_contains($name, '|')) {
            foreach (explode('|', $name) as $type) {
                if ($type = trim($type)) {
                    if ($type === 'null') {
                        $this->nullable = true;
                    } else {
                        $this->types[] = new self($type);
                    }
                }
            }
        }
    }

    /**
     * Creates a Type object from a ReflectionType object.
     *
     * @param \ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType $reflection The reflection type.
     * @return static The created Type object.
     */
    public static function fromReflectionType(
        \ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType $reflection
    ): static {
        if ($reflection instanceof \ReflectionNamedType) {
            $name = $reflection->getName();
            if ($reflection->allowsNull()) {
                $name .= '|null';
            }
            return new static($name);
        }

        $instance = new static('');

        $types = [];
        foreach (static::intersectionTypes($reflection->getTypes()) as $type) {
            if ($name = $type->getName()) {
                $types[] = $name;
                $instance->types[] = new static($name);
            }
        }

        if ($reflection->allowsNull()) {
            $types[] = 'null';
            $instance->types[] = new static('null');
        }

        $instance->name = implode('|', $types);
        return $instance;
    }

    /**
     * Recursively extracts named types from a list of intersection types.
     *
     * @param array $intersection The array of reflection types.
     * @return array<\ReflectionNamedType> The array of named types.
     */
    public static function intersectionTypes(array $intersection): array
    {
        $types = [];

        foreach ($intersection as $item) {
            if ($item instanceof \ReflectionNamedType) {
                $types[] = $item;
            } elseif (is_array($item)) {
                array_push($types, ...static::intersectionTypes($item));
            } elseif (
                $item instanceof \ReflectionUnionType
                || $item instanceof \ReflectionIntersectionType
            ) {
                array_push($types, ...static::intersectionTypes($item->getTypes()));
            }
        }

        return $types;
    }

    /**
     * Returns the string representation of the Type object.
     *
     * @return string The name of the type.
     */
    public function __toString(): string
    {
        return $this->name .
            ((!$this->nullable || str_contains($this->name, 'null')) ? '' : '|null');
    }

    /**
     * Retrieves an array of Type objects if the current type is a union type,
     * otherwise returns an array containing only the current Type object.
     *
     * @return array An array of Type objects.
     */
    public function getTypes(): array
    {
        return $this->types ?: [$this];
    }

    /**
     * Returns the name of the type.
     *
     * @return string The name of the type.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Checks if the type allows null values.
     *
     * @return bool True if the type allows null, false otherwise.
     */
    public function allowsNull(): bool
    {
        foreach ($this->types as $type) {
            if ($type->allowsNull()) {
                return true;
            }
        }
        return $this->nullable;
    }

    /**
     * Checks if the type is a union type (contains multiple types).
     *
     * @return bool True if the type is a union type, false otherwise.
     */
    public function isUnion(): bool
    {
        return count($this->types) > 1;
    }

    /**
     * Checks if the type is valid, i.e., built-in, class, interface, or enum type.
     *
     * @return bool True if the type is valid, false otherwise.
     */
    public function isValid(): bool
    {
        return $this->isBuiltin() || $this->isClass() || $this->isInterface() || $this->isEnum();
    }

    /**
     * Checks if the type is a built-in PHP type.
     *
     * @return bool True if the type is a built-in PHP type, false otherwise.
     */
    public function isBuiltin(): bool
    {
        return in_array($this->name, self::RETURN_TYPES);
    }

    /**
     * Checks if the type is a class or enum.
     *
     * @return bool True if the type is a class or enum, false otherwise.
     */
    public function isClass(): bool
    {
        return class_exists($this->name) || enum_exists($this->name);
    }

    /**
     * Checks if the type is an interface.
     *
     * @return bool True if the type is an interface, false otherwise.
     */
    public function isInterface(): bool
    {
        return interface_exists($this->name);
    }

    /**
     * Checks if the type is an enum.
     *
     * @return bool True if the type is an enum, false otherwise.
     */
    public function isEnum(): bool
    {
        return enum_exists($this->name);
    }

    /**
     * Checks if the type is a scalar type.
     *
     * @return bool True if the type is a scalar type, false otherwise.
     */
    public function isScalar(): bool
    {
        return in_array($this->name, self::SCALAR_TYPES);
    }

    /**
     * Checks if the type is a compound type.
     *
     * @return bool True if the type is a compound type, false otherwise.
     */
    public function isCompound(): bool
    {
        return in_array($this->name, self::COMPOUND_TYPES);
    }

    /**
     * Checks if the type is a special type.
     *
     * @return bool True if the type is a special type, false otherwise.
     */
    public function isSpecial(): bool
    {
        return in_array($this->name, self::SPECIAL_TYPES);
    }

    /**
     * Checks if the type is nullable (null).
     *
     * @return bool True if the type is nullable (null), false otherwise.
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }
}
