<?php

namespace Autumn\System;

use Autumn\Attributes\JsonIgnore;
use Autumn\Interfaces\ArrayInterface;
use Autumn\Interfaces\ModelInterface;
use Autumn\System\ServiceContainer\DecoratorProxy;
use Autumn\Traits\ModelPropertiesTrait;

class Model implements ModelInterface, \ArrayAccess, \JsonSerializable, ArrayInterface
{
    use ModelPropertiesTrait;

    public static function from(array $data): static
    {
        if ($class = DecoratorProxy::createProxyClass(static::class)) {
            return (new $class)->fromArray($data);
        }

        return (new static)->fromArray($data);
    }

    public function fromArray(array $data): static
    {
        foreach ($data as $name => $value) {
            if (is_string($name)) {
                $this->$name = $value;
            }
        }

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        $data = [];
        foreach (static::__properties__() as $name => $property) {
            if (!$property->isStatic()) {
                foreach ($property->getAttributes(JsonIgnore::class) as $attribute) {
                    $value = $attribute->newInstance()->ignorable($property, $this);
                    if ($value !== null) {
                        $data[$name] = $value;
                        continue 2;
                    }
                }

                $data[$name] = $this->__get($name);
            }
        }
        return $data;
    }

    public function toArray(): array
    {
        $data = [];
        foreach (static::__properties__() as $name => $property) {
            if (!$property->isStatic()) {
                $data[$name] = $this->__get($name);
            }
        }
        return $data;
    }

    public function offsetExists(mixed $offset): bool
    {
        return is_string($offset) && $this->__isset($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return is_string($offset) ? $this->__get($offset) : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        is_string($offset) && $this->__set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->offsetSet($offset, null);
    }
}