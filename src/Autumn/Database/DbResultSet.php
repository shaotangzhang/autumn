<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Interfaces\DriverInterface;
use Autumn\Exceptions\ValidationException;
use Autumn\System\Model;
use Autumn\System\Reflection;

class DbResultSet implements \IteratorAggregate, \Stringable
{
    private mixed $result = null;
    private mixed $callback = null;
    private ?int $mode = null;
    private ?string $primaryTableAlias = null;

    private array $cache = [];
    private bool $cacheDone = false;
    private bool $cacheResultSet = false;

    private array $resultMeta = [];

    public function __construct(private DriverInterface $driver, private string $sql, private ?array $parameters = null)
    {
    }

    public function __toString(): string
    {
        return $this->sql;
    }

    public function getQueryString(): string
    {
        return $this->sql;
    }

    public function getParameters(): array
    {
        return $this->parameters ?? [];
    }

    public function callback(string|callable $callback = null): static
    {
        $this->callback = $callback;
        return $this;
    }

    public function mode(int $mode): static
    {
        $this->mode = $mode;
        return $this;
    }

    public function alias(string $alias): static
    {
        $this->primaryTableAlias = $alias;
        return $this;
    }

    public function cache(bool $cacheResultSet): static
    {
        $this->cacheResultSet = $cacheResultSet;
        return $this;
    }

    /**
     * @throws DbException
     */
    public function execute(array $parameters = null): int
    {
        if ($statement = $this->driver->exec($this->sql, $parameters ?? $this->parameters ?? [])) {
            return $this->driver->getAffectedRows($statement);
        }

        if ($exception = $this->driver->getException()) {
            throw $exception;
        }

        return 0;
    }

    public function exists(): bool
    {
        if ($this->result ??= $this->driver->exec($this->sql, $this->parameters ?? [])) {
            return $this->driver->exists($this->result);
        }

        return false;
    }

    public function fetch(string|callable $callback = null, int $mode = null): mixed
    {
        if (($mode ?? $this->mode) === Db::FETCH_DATA) {
            $rs = $this->fetchData($this->primaryTableAlias);
        } else {
            $this->result ??= $this->driver->exec($this->sql, $this->parameters ?? []);
            if ($rs = $this->driver->fetch($this->result, $mode ?? $this->mode)) {
                if ($this->cacheResultSet) {
                    $this->cache[] = $rs;
                }
            } else {
                $this->cacheDone = true;
            }
        }

        if ($rs) {
            if ($callback ??= $this->callback) {
                if (empty($callback)) {
                    return (object)$rs;
                }

                if (is_callable($callback)) {
                    return call_user_func($callback, $rs);
                }

                if (is_string($callback)) {
                    if (is_subclass_of($callback, Model::class, true)) {
                        return $callback::from($rs);
                    }
                }
            }
        }

        return $rs;
    }

    public function fetchAssoc(): ?array
    {
        return $this->fetch('', Db::FETCH_ASSOC);
    }

    public function fetchArray(): ?array
    {
        return $this->fetch('', Db::FETCH_NUM);
    }

    public function fetchBoth(): ?array
    {
        return $this->fetch('', Db::FETCH_BOTH);
    }

    public function fetchObject(string $class = null, array $params = null): ?object
    {
        if ($class && !class_exists($class)) {
            throw new \RuntimeException("Class `$class` is not found.");
        }

        if ($rs = $this->fetchAssoc()) {
            if ($params) {
                $rs += $params;
            }

            if (!$class) {
                return (object)$rs;
            }

            $instance = new $class;
            foreach ($rs as $name => $value) {
                $instance->$name = $value;
            }

            return $instance;
        }

        return null;
    }

    public function fetchColumn(int|string $column = null): mixed
    {
        if (is_int($column)) {
            $rs = $this->fetchArray();
        } else {
            $rs = $this->fetchAssoc();
        }

        if ($rs) {
            return $column ? $rs[$column] ?? null : reset($rs);
        }

        return null;
    }

    public function fetchColumnList(int|string $column = null): \Traversable
    {
        if (is_int($column)) {
            while ($rs = $this->fetchArray()) {
                yield $rs[$column] ?? null;
            }
        } elseif ($column) {
            while ($rs = $this->fetchAssoc()) {
                yield $rs[$column] ?? null;
            }
        } else {
            while ($rs = $this->fetchAssoc()) {
                yield reset($rs);
            }
        }
    }

    /**
     * @return array<string, Column>|null
     */
    public function fetchMeta(): ?array
    {
        if (!$this->result) {
            return null;
        }

        $id = spl_object_id($this->result);
        if (!isset($this->resultMeta[$id])) {
            $this->resultMeta[$id] = $this->fetch('', Db::FETCH_META);
        }

        return $this->resultMeta[$id];
    }

    public function fetchData(string $primary = null): ?array
    {
        $rs = $this->fetchArray();
        if (!$rs) {
            return null;
        }

        $data = [];
        foreach ($this->fetchMeta() as $index => $column) {
            $table = $column->getTable();
            if ($primary === $table) {
                $data[$column->getName()] = $rs[$index];
            } else {
                $data[$table][$column->getName()] = $rs[$index];
            }
        }
        return $data;
    }

    public function getIterator(): \Traversable
    {
        if ($this->cacheDone) {
            yield from $this->cache;
        } else {
            while ($rs = $this->fetch($this->callback, $this->mode)) {
                yield $rs;
            }

            if ($this->cacheResultSet) {
                $this->cacheDone = true;
            }
        }
    }

    public function limit(int $limit, int $page = null): static
    {
        if ($limit < 1) {
            throw ValidationException::of('The record number per page must be greater than zero.');
        }

        $sql = preg_replace('/\b(LIMIT\s+\d+(\s*,\s*\d+)?|OFFSET\s+\d+)/i', '', $this->sql);
        $sql .= ' LIMIT ' . $limit . ', ' . (max($page, 1) - 1) * $limit;

        return new static($this->driver, $sql, $this->parameters);
    }
}