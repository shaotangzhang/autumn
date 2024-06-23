<?php
/**
 * Autumn PHP Framework
 *
 * Date:        20/06/2024
 */

namespace Autumn\Caching;

use Autumn\Database\Interfaces\Persistable;
use Autumn\Interfaces\CachingInterface;
use Autumn\Interfaces\PersistingInterface;

class Cache implements CachingInterface, PersistingInterface
{
    public const DEFAULT_TTL = 300;

    private static array $instances = [];

    /**
     * @var array<string, array<int, Persistable>>
     */
    private static array $buffers = [];

    public function __construct(private readonly string $prefix = '',
                                private readonly int    $ttl = self::DEFAULT_TTL)
    {
    }

    public static function forClass(string $class): static
    {
        $class = strtr(trim($class, '/\\'), '\\', '/');
        return static::$instances[$class] ??= new static($class);
    }

    /**
     * @return string|null
     */
    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function has(string $key, int $ttl = null): bool
    {
        $expires = self::$buffers[$this->prefix][$key]['expires'] ?? null;

        if ($expires !== null) {
            return !$expires || ($expires >= (time() - ($ttl ?? $this->ttl)));
        }

        foreach ($this->query(compact('key')) as $offset => $item) {
            if ($key === $offset) {
                return true;
            }
        }

        return false;
    }

    public function get(string $key): mixed
    {
        if ($item = self::$buffers[$this->prefix][$key] ?? null) {
            return $item['data'] ?? null;
        }

        foreach ($this->query(compact('key')) as $offset => $item) {
            if ($key === $offset) {
                return $item['data'] ?? null;
            }
        }

        return null;
    }

    public function set(string $key, mixed $value, int $ttl = null): bool
    {
        self::$buffers[$this->prefix][$key]['data'] = $value;
        self::$buffers[$this->prefix][$key]['expires'] = time() + $ttl ?? $this->ttl;
        return $this->persist(self::$buffers[$this->prefix][$key]);
    }

    public function delete(string $key): bool
    {
        if ($item = self::$buffers[$this->prefix][$key] ?? null) {
            if ($id = $item['id'] ?? null) {
                if (!$this->destroy($id)) {
                    return false;
                }
            }
        }

        unset(self::$buffers[$this->prefix][$key]);
        return true;
    }

    public function persist(Persistable $persistable): bool
    {
        return false;
    }

    public function destroy(int $id): bool
    {
        return false;
    }

    public function exists(array $context): bool
    {
        return false;
    }

    public function query(array $context): iterable
    {
        return null;
    }
}