<?php

namespace Autumn\Database\Models;

use Autumn\Database\DbConnection;
use Autumn\Database\DbResultSet;
use Autumn\Database\Interfaces\EntityInterface;
use Autumn\Database\Interfaces\Extendable;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Traits\RepositoryTrait;
use Autumn\Exceptions\ValidationException;
use Stringable;

class Repository implements RepositoryInterface
{
    use RepositoryTrait;

    private string $model;
    private string $table;

    public function __construct(string $class, array $options = [])
    {
        if (!is_subclass_of($class, EntityInterface::class)) {
            throw ValidationException::of('The first argument must be the class of an EntityInterface, `%s` is given.', $class);
        }

        $this->model = $class;
        $this->table = $class::entity_name();

        foreach ($options as $name => $value) {
            if (method_exists($this, $func = 'set' . $name)) {
                $this->$func($value);
            }
        }
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable(string $table): void
    {
        $this->table = $table;
    }

    /**
     * @return DbConnection|null
     */
    public function getConnection(): ?DbConnection
    {
        return $this->connection;
    }

    public function data(): ?array
    {
        return $this->lastResult?->fetchData();
    }

    public function array(): ?array
    {
        return $this->lastResult?->fetchAssoc();
    }

    public function object(): mixed
    {
        return $this->lastResult?->fetchObject($this->model);
    }

    public function find(array|int $criteria = null): mixed
    {
        return $this->findBy($criteria)->object();
    }

    public function findBy(array|int $criteria): static
    {
        return $this->query();
    }

    public function findOrFail(array|int $criteria, string $message = null, int $statusCode = null): static
    {
        // TODO: Implement findOrFail() method.
    }

    public function from(array $data): static
    {
        // TODO: Implement from() method.
    }

    public function select(string|Stringable ...$columns): static
    {
        // TODO: Implement select() method.
    }

    public function and(Stringable|string $condition, mixed $value = null): static
    {
        // TODO: Implement and() method.
    }

    public function where(Stringable|string $condition, string $operator = null, mixed $value = null): static
    {
        // TODO: Implement where() method.
    }

    public function orderBy(Stringable|string $column, bool $desc = null): static
    {
        // TODO: Implement orderBy() method.
    }

    public function groupBy(string|Stringable ...$columns): static
    {
        // TODO: Implement groupBy() method.
    }

    public function having(Stringable|string $condition, string $operator = null, mixed $value = null): static
    {
        // TODO: Implement having() method.
    }

    public function limit(int $limit = null, int $page = null): static
    {
        // TODO: Implement limit() method.
    }

    public function join(Stringable|string $table, string $type = null, Stringable|string $localKey = null, Stringable|string $foreignKey = null): static
    {
        // TODO: Implement join() method.
    }

    public function innerJoin(Stringable|string $table, Stringable|string $localKey = null, Stringable|string $foreignKey = null): static
    {
        // TODO: Implement innerJoin() method.
    }

    public function leftJoin(Stringable|string $table, Stringable|string $localKey = null, Stringable|string $foreignKey = null): static
    {
        // TODO: Implement leftJoin() method.
    }

    public function rightJoin(Stringable|string $table, Stringable|string $localKey = null, Stringable|string $foreignKey = null): static
    {
        // TODO: Implement rightJoin() method.
    }

    public function outerJoin(Stringable|string $table, Stringable|string $localKey = null, Stringable|string $foreignKey = null): static
    {
        // TODO: Implement outerJoin() method.
    }

    public function query(): static
    {
        $this->lastResult = null;

        // do query

        return $this;
    }

    public function command(string $command, string|Stringable ...$columns): static
    {
        // TODO: Implement command() method.
    }

    public function slice(int $limit, int $page = null): static
    {
        // TODO: Implement slice() method.
    }

    public function chunk(int $limit): iterable
    {
        // TODO: Implement chunk() method.
    }

    public function count(Stringable|string $column = null): int
    {
        // TODO: Implement count() method.
    }

    public function exists(): bool
    {
        // TODO: Implement exists() method.
    }

    public function alias(string $alias = null): static
    {
        // TODO: Implement alias() method.
    }

    public function aliasName(): string
    {
        // TODO: Implement aliasName() method.
    }

    public function paginate(): ?array
    {
        // TODO: Implement paginate() method.
    }

    public function bind(string $name, mixed $value): static
    {
        // TODO: Implement bind() method.
    }

    public function callback(callable|string $callback = null, int $mode = null): static
    {
        // TODO: Implement callback() method.
    }

    public function through(Extendable|string $relation, string $relationAlias, string $theOtherAliasOfRelation = null): static
    {
        // TODO: Implement through() method.
    }
}