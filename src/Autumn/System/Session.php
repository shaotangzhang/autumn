<?php

namespace Autumn\System;

use Autumn\System\Sessions\DefaultSession;
use Autumn\System\Sessions\SessionInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class Session
 *
 * Provides static methods for interacting with sessions, using a SessionInterface for session management.
 */
final class Session
{
    private static ?SessionInterface $session = null;

    /**
     * Get the current session context.
     *
     * @return SessionInterface
     */
    public static function context(): SessionInterface
    {
        return self::$session ??= make(SessionInterface::class, null, true) ?? DefaultSession::context();
    }

    /**
     * Execute a callback with the session context, closing the session afterward.
     *
     * @param callable $callback
     * @return mixed
     */
    public static function mute(callable $callback): mixed
    {
        self::context()->close();
        return call_user_func($callback, self::context());
    }

    /**
     * Execute a callback with the session context, ensuring the session is closed afterwards.
     *
     * @param callable $callback
     * @return mixed
     */
    public static function enclosed(callable $callback): mixed
    {
        try {
            return call_user_func($callback, self::context());
        } finally {
            self::context()->close();
        }
    }

    /**
     * Get a session value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::context()->get($key, $default);
    }

    /**
     * Set a session value by key.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function set(string $key, mixed $value): bool
    {
        return self::context()->set($key, $value);
    }

    /**
     * Delete a session value by key.
     *
     * @param string $key
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function delete(string $key): bool
    {
        return self::context()->delete($key);
    }

    /**
     * Clear all session values.
     *
     * @return bool
     */
    public static function clear(): bool
    {
        return self::context()->clear();
    }

    /**
     * Check if a session key exists.
     *
     * @param string $key
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function has(string $key): bool
    {
        return self::context()->has($key);
    }

    /**
     * Get the current session ID.
     *
     * @return string
     */
    public static function id(): string
    {
        return self::context()->id();
    }

    /**
     * Abort the session.
     */
    public static function abort(): void
    {
        self::context()->abort();
    }

    /**
     * Close the session.
     */
    public static function close(): void
    {
        self::context()->close();
    }

    /**
     * Destroy the session.
     */
    public static function destroy(): void
    {
        self::context()->destroy();
    }
}
