<?php

namespace Autumn\Interfaces;

interface CachingInterface
{
    /**
     * Retrieve a cached value by key.
     *
     * @param string $key The key of the cached value.
     * @return mixed|null The cached value if exists, or null if not found.
     */
    public function get(string $key): mixed;

    /**
     * Set a cached value with key and optional TTL.
     *
     * @param string $key The key of the cached value.
     * @param mixed $value The value to cache.
     * @param int $ttl Time-to-live in seconds (optional, 0 means no expiration).
     * @return bool
     */
    public function set(string $key, mixed $value, int $ttl = 0): bool;

    /**
     * Check if a cached value exists for the given key.
     *
     * @param string $key The key to check.
     * @return bool True if the key exists in the cache, false otherwise.
     */
    public function has(string $key): bool;

    /**
     * Delete a cached value by key.
     *
     * @param string $key The key of the cached value to delete.
     * @return bool True if the value was successfully deleted, false otherwise.
     */
    public function delete(string $key): bool;
}