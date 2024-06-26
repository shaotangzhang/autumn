<?php
/**
 * Autumn PHP Framework
 *
 * Date:        26/06/2024
 */

namespace Autumn\Traits;

use Autumn\Attributes\Transient;

trait ArrayPropertiesTrait
{
    #[Transient]
    private array $properties = [];

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->properties[$name] ?? $default;
    }

    public function set(string $name, mixed $value): void
    {
        $this->properties[$name] = $value;
    }

    public function has(string $name): bool
    {
        return isset($this->properties[$name]);
    }

    public function toArray(): array
    {
        return $this->properties;
    }

    public function __get(string $name): mixed
    {
        return $this->properties[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->properties[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->properties[$name]);
    }

    public function __unset(string $name): void
    {
        unset($this->properties[$name]);
    }
}