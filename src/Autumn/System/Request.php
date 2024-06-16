<?php

namespace Autumn\System;

class Request implements \ArrayAccess
{
    public static ?self $instance = null;

    public static function capture(): static
    {
        return self::$instance ??= new static;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($_REQUEST[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $_REQUEST[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $_REQUEST[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($_REQUEST[$offset]);
    }
}
