<?php

namespace Autumn\System\Sessions;

use Autumn\Exceptions\SystemException;
use Autumn\Interfaces\ContextInterface;
use Autumn\Traits\ContextInterfaceTrait;
use SessionHandlerInterface;
use Traversable;

/**
 * Class DefaultSession
 *
 * A default implementation of SessionInterface for managing PHP sessions.
 */
class DefaultSession implements SessionInterface, ContextInterface
{
    use ContextInterfaceTrait;

    public function __construct(private readonly array $options = [])
    {

    }

    /**
     * Creates a default instance using environment variables prefixed with 'SESSION_'.
     *
     * @return static
     */
    protected static function createDefaultInstance(): static
    {
        $options = [];
        foreach ($_ENV as $name => $value) {
            if (str_starts_with($name, 'SESSION_')) {
                $options[strtolower(substr($name, 8))] = $value;
            }
        }
        return new static($options);
    }

    /**
     * Retrieve a session value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->open();

        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set a session value by key.
     *
     * @param string $key
     * @param mixed $value
     * @param \DateInterval|int|null $ttl
     * @return bool
     */
    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        $this->open();

        $_SESSION[$key] = $value;

        return true;
    }

    /**
     * Delete a session value by key.
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        $this->open();

        unset($_SESSION[$key]);
        return true;
    }

    /**
     * Clear all session values.
     *
     * @return bool
     */
    public function clear(): bool
    {
        $this->open();

        return session_unset();
    }

    /**
     * Retrieve multiple session values by keys.
     *
     * @param iterable $keys
     * @param mixed $default
     * @return iterable
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $this->open();

        $data = [];
        foreach ($keys as $key) {
            $data[$key] = $_SESSION[$key] ?? $default;
        }
        return $data;
    }

    /**
     * Set multiple session values.
     *
     * @param iterable $values
     * @param \DateInterval|int|null $ttl
     * @return bool
     */
    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        $this->open();

        foreach ($values as $key => $value) {
            $_SESSION[$key] = $value;
        }

        return true;
    }

    /**
     * Delete multiple session values by keys.
     *
     * @param iterable $keys
     * @return bool
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $this->open();

        foreach ($keys as $key) {
            unset($_SESSION[$key]);
        }

        return true;
    }

    /**
     * Check if a session key exists.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $this->open();

        return isset($_SESSION[$key]);
    }

    /**
     * Get an iterator for all session values.
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        $this->open();

        return new \ArrayIterator($_SESSION);
    }

    /**
     * Get the current session ID.
     *
     * @return string
     */
    public function id(): string
    {
        return session_id() ?: '';
    }

    /**
     * Open the session.
     *
     * @param array|null $options
     * @throws \RuntimeException if session is disabled or fails to start
     */
    public function open(array $options = null): void
    {
        if (!isset($_SESSION) || !session_id()) {
            switch (session_status()) {
                case PHP_SESSION_DISABLED:
                    throw new \RuntimeException('The session is disabled.');

                case PHP_SESSION_NONE:

                    if ($options ??= $this->options) {
                        $this->setSaveHandler($options['save_handler'] ?? null);
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

    private function setSaveHandler(mixed $saveHandler): void
    {
        if (is_subclass_of($saveHandler, SessionHandlerInterface::class)) {
            if (is_string($saveHandler)) {
                $saveHandler = make($saveHandler);
            }
        }

        if (!$saveHandler) {
            $saveHandler = make(SessionHandlerInterface::class);
        }

        if ($saveHandler instanceof SessionHandlerInterface) {
            // Set the session save handler
            if (!session_set_save_handler($saveHandler, true)) {
                throw SystemException::of("Failed to set session save handler `%s`.", $saveHandler::class);
            }
        }
    }

    /**
     * Abort the session.
     */
    public function abort(): void
    {
        session_abort();
    }

    /**
     * Close the session and write session data.
     */
    public function close(): void
    {
        session_write_close();
    }

    /**
     * Destroy all session data and delete the session cookie.
     */
    public function destroy(): void
    {
        session_destroy();
    }
}
