<?php
namespace Autumn\System;

class Session implements \ArrayAccess, \IteratorAggregate
{
    public function __construct(private readonly string $prefix = '')
    {
    }

    public static function options(): array
    {
        $options = [];

        if (($lifetime = (int)env('SESSION_LIFETIME', 3600)) > 0) {
            $options['cookie_lifetime'] = $lifetime;
        }

        return $options;
    }

    public static function start(): void
    {
        if (!isset($_SESSION) || !session_id()) {

            switch (session_status()) {
                case PHP_SESSION_DISABLED:
                    throw new \RuntimeException('The session is disabled.');

                case PHP_SESSION_NONE:
                    if ($options = static::options()) {
                        $started = session_start($options);
                    } else {
                        $started = session_start();
                    }

                    if (!$started) {
                        throw new \RuntimeException('Failed to start the session.');
                    }
                    break;

                default:
                    if (!isset($_SESSION)) {
                        $_SESSION = [];
                    }
            }
        }
    }

    public static function close(): void
    {
        // if (session_id()) {
        session_write_close();
        // }
    }

    public static function abort(): void
    {
        if (session_id()) {
            session_abort();
        }
    }

    public static function destroy(): void
    {
        static::start();
        session_destroy();
    }

    public static function silent(callable $callable, mixed ...$args): mixed
    {
        if (session_id()) {
            try {
                session_write_close();
                return call_user_func_array($callable, $args);
            } finally {
                static::start();
            }
        } else {
            return call_user_func_array($callable, $args);
        }
    }

    public static function get(string $name, mixed $default = null): mixed
    {
        static::start();
        return $_SESSION[$name] ?? $default;
    }

    public static function has(string $name): bool
    {
        static::start();
        return isset($_SESSION[$name]);
    }

    public static function set(string $name, mixed $value): void
    {
        static::start();
        $_SESSION[$name] = $value;
    }

    public static function remove(string $name): void
    {
        static::start();
        unset($_SESSION[$name]);
    }

    public static function all(): array
    {
        static::start();
        return $_SESSION;
    }

    public static function id(): string
    {
        return session_id();
    }

    public static function name(): string
    {
        return session_name();
    }

    public static function transactional(callable $callback, mixed ...$args): mixed
    {
        $id = session_id();
        try {
            return call_user_func($callback, new static, ...$args);
        } finally {
            if (!$id && session_id()) {
                session_write_close();
            }
        }
    }

    public function getIterator(): \Traversable
    {
        if ($this->prefix) {
            $all = Session::get($this->prefix);
            if (is_iterable($all)) {
                yield from $all;
            }
        } else {
            yield from Session::all();
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        Session::start();

        if ($this->prefix) {
            return isset($_SERVER[$this->prefix][$offset]);
        } else {
            return isset($_SERVER[$offset]);
        }
    }

    public function offsetGet(mixed $offset): mixed
    {
        Session::start();

        if ($this->prefix) {
            return $_SERVER[$this->prefix][$offset] ?? null;
        } else {
            return $_SERVER[$offset] ?? null;
        }
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        Session::start();

        if ($this->prefix) {
            $_SERVER[$this->prefix][$offset] = $value;
        } else {
            $_SERVER[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        Session::start();

        if ($this->prefix) {
            unset($_SERVER[$this->prefix][$offset]);
        } else {
            unset($_SERVER[$offset]);
        }
    }
}