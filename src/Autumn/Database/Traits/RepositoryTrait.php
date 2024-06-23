<?php
/**
 * Autumn PHP Framework
 *
 * Date:        8/05/2024
 */

namespace Autumn\Database\Traits;

use Autumn\Attributes\Transient;
use Autumn\Database\Db;
use Autumn\Database\DbConnection;
use Autumn\Database\DbResultSet;
use Autumn\Database\Interfaces\EntityInterface;
use Autumn\Database\Interfaces\EntityManagerInterface;
use Autumn\Database\Interfaces\Extendable;
use Autumn\Database\Interfaces\Recyclable;
use Autumn\Database\Interfaces\RecyclableEntityManagerInterface;
use Autumn\Database\Interfaces\RelationInterface;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Models\AbstractEntity;
use Autumn\Database\Models\Relation;
use Autumn\Events\Event;
use Autumn\Exceptions\NotFoundException;
use Autumn\Exceptions\SystemException;
use Autumn\Exceptions\ValidationException;
use Stringable;
use Traversable;

class RepositoryTrait implements RepositoryInterface
{
    #[Transient]
    private array $relationObjects = [];

    #[Transient]
    private array $query = [];

    #[Transient]
    private array $parameters = [];

    #[Transient]
    private string $primaryTableAlias = '';

    #[Transient]
    private string $manipulate = 'SELECT';

    #[Transient]
    private bool $toCacheResultSet = false;

    #[Transient]
    private mixed $callback = null;

    #[Transient]
    private ?int $mode = null;

    #[Transient]
    private ?DbConnection $connection = null;

    #[Transient]
    private ?DbResultSet $resultSet = null;


    public function getModelPrimaryKey(): string
    {
        return '';
    }

    private function fire(string $event, mixed ...$args): bool
    {
        return Event::fire($event, $this, $args);
    }

    public function getConnection(): ?DbConnection
    {
        return $this->connection;
    }

    public function withConnection(DbConnection $connection): static
    {
        if ($this->connection === $connection) {
            return $this;
        }

        $clone = clone $this;
        $clone->connection = $connection;
        return $clone;
    }

    public function find(int|array $criteria = null): mixed
    {
        return static::findBy($criteria ?? [])->first();
    }

    public function findBy(int|array $criteria): static
    {
        if (is_int($criteria)) {
            if ($column = $this->getModelPrimaryKey()) {
                $criteria = [$column => $criteria];
            } else {
                $criteria = [$criteria];
            }
        }

        return $this->createQuery($criteria, ['limit' => 1, 'page' => 1]);
    }

    public function findOrFail(int|array $criteria, string $message = null, int $statusCode = null): static
    {
        return static::find($criteria)
            ?? throw new NotFoundException($message, $statusCode ?? 404);
    }

    public function findOrNew(array $criteria): static
    {
        return static::find($criteria)
            ?? $this->createModelInstance($criteria);
    }

    public function findOrCreate(array $criteria, array $extra = null): ?static
    {
        return static::find($criteria)
            ?? $this->createFrom(array_merge($extra ?? [], $criteria));
    }

    public function createQuery(array $criteria, array $context = null, array $parameters = []): static
    {
        $this->resultSet = null;
        $this->parameters = $parameters;
        $this->query = [];
        return $this->select();
    }

    public function select(string|Stringable ...$columns): static
    {
        $this->manipulate = 'SELECT';
        $this->query['select'] = $columns;
        return $this;
    }

    public function and(string|Stringable $condition, mixed $value = null): static
    {
        return $this->where($condition, null, $value);
    }

    public function hasWhere(string|Stringable $condition, string $operator = null, mixed $value = null): ?int
    {
        $pos = array_search([$condition, $operator, $value], $this->query['where'] ?? [], true);
        return ($pos === false) ? null : $pos;
    }

    public function removeWhere(string|Stringable $condition, string $operator = null, mixed $value = null): static
    {
        $pos = array_search([$condition, $operator, $value], $this->query['where'] ?? [], true);
        if ($pos !== false) {
            unset($this->query['where'][$pos]);
        }
        return $this;
    }

    public function whereIfNotSet(string|Stringable $condition, string $operator = null, mixed $value = null): static
    {
        $where = [$condition, $operator, $value];
        if (!in_array($where, $this->query['where'] ?? [], true)) {
            $this->query['where'][] = $where;
        }
        return $this;
    }

    public function where(string|Stringable $condition, string $operator = null, mixed $value = null): static
    {
        $this->query['where'][] = [$condition, $operator, $value];
        return $this;
    }

    public function orderBy(string|Stringable $column, bool $desc = null): static
    {
        $this->query['orderBy'][$column] = $desc;
        return $this;
    }

    public function groupBy(string|Stringable ...$columns): static
    {
        foreach ($columns as $column) {
            $this->query['groupBy'][] = $column;
        }
        return $this;
    }

    public function having(string|Stringable $condition, string $operator = null, mixed $value = null): static
    {
        $this->query['having'][] = [$condition, $operator, $value];
        return $this;
    }

    public function limit(int $limit = null, int $page = null): static
    {
        $this->query['limit'] = $limit;
        $this->offset($page === null ? null : (max(0, $page - 1) * $limit));
        return $this;
    }

    public function offset(int $offset = null): static
    {
        $this->query['offset'] = $offset;
        return $this;
    }

    public function join(string|Stringable $table, string $type = null, string|Stringable $localKey = null, string|Stringable $foreignKey = null): static
    {
        $this->query['join'][] = [$type, $table, $localKey, $foreignKey];
        return $this;
    }

    public function innerJoin(string|Stringable $table, string|Stringable $localKey = null, string|Stringable $foreignKey = null): static
    {
        return $this->join($table, 'INNER', $localKey, $foreignKey);
    }

    public function leftJoin(string|Stringable $table, string|Stringable $localKey = null, string|Stringable $foreignKey = null): static
    {
        return $this->join($table, 'LEFT', $localKey, $foreignKey);
    }

    public function rightJoin(string|Stringable $table, string|Stringable $localKey = null, string|Stringable $foreignKey = null): static
    {
        return $this->join($table, 'RIGHT', $localKey, $foreignKey);
    }

    public function outerJoin(string|Stringable $table, string|Stringable $localKey = null, string|Stringable $foreignKey = null): static
    {
        return $this->join($table, 'OUTER', $localKey, $foreignKey);
    }

    protected function buildWhereConditions(array $conditions, array &$parameters, int &$paramIndex = 0, string $paramPrefix = null): string
    {
        $where = null;

        foreach ($conditions as [$condition, $operator, $value]) {
            if ($value === null && $operator === null) {
                $where[] = $condition;
            } elseif ($value === null) {
                $where[] = "$condition $operator NULL";
            } else {
                $param = $paramPrefix . $paramIndex++;
                $parameters[$param] = $value;
                if (!$operator) {
                    $operator = is_array($value) ? 'IN' : '=';
                }
                $where[] = "$condition $operator :$param";
            }
        }

        return $where ? implode(' AND ', $where) : '';
    }

    protected function buildFrom(): array
    {
        $sql = null;

        if ($this->primaryTableAlias) {
            $sql[] = $this->getTable() . ' AS ' . $this->primaryTableAlias;
        } else {
            $sql[] = $this->getTable();
        }

        if (is_array($from = $this->query['from'] ?? null)) {
            foreach ($from as $alias => $table) {
                if (is_string($alias)) {
                    $sql[] = "$table AS $alias";
                } else {
                    $sql[] = $table;
                }
            }
        } elseif (is_string($from)) {
            $sql[] = $from;
        }

        return ['FROM', implode(', ', $sql)];
    }

    protected function buildJoin(): array
    {
        $sql = [];

        if (isset($this->query['join'])) {
            foreach ($this->query['join'] as [$type, $table, $localKey, $foreignKey]) {
                $sql[] = "$type JOIN $table ON $localKey = $foreignKey";
            }
        }

        return $sql;
    }

    protected function buildWhere(array &$parameters, int &$paramIndex = 0, string $paramPrefix = null): array
    {
        $sql = [];

        if (isset($this->query['where'])) {
            $where = $this->buildWhereConditions($this->query['where'], $parameters, $paramIndex, $paramPrefix);

            if ($where) {
                $sql[] = 'WHERE';
                $sql[] = $where;
            }
        }

        return $sql;
    }

    protected function buildGroupBy(array &$parameters, int &$paramIndex = 0, string $paramPrefix = null): array
    {
        $sql = [];

        if (isset($this->query['groupBy'])) {
            $sql[] = 'GROUP BY';
            $sql[] = implode(',', $this->query['groupBy']);

            if (isset($this->query['having'])) {
                $having = $this->buildWhereConditions($this->query['having'], $parameters, $paramIndex, $paramPrefix);

                if ($having) {
                    $sql[] = 'HAVING';
                    $sql[] = $having;
                }
            }
        }

        return $sql;
    }

    protected function buildOrderBy(): array
    {
        $sql = [];

        if (isset($this->query['orderBy'])) {
            $orders = null;
            foreach ($this->query['orderBy'] as $column => $desc) {
                $orders[] = $desc ? "$column DESC" : $column;
            }

            if ($orders) {
                $sql[] = 'ORDER BY';
                $sql[] = implode(',', $orders);
            }
        }

        return $sql;
    }

    protected function buildLimitOffset(): array
    {
        $sql = [];

        if (isset($this->query['limit'])) {
            $sql[] = 'LIMIT';
            $sql[] = $this->query['limit'];
        }

        if (isset($this->query['offset'])) {
            $sql[] = 'OFFSET';
            $sql[] = $this->query['offset'];
        }

        return $sql;
    }

    protected function buildSelect(): array
    {
        $select = $this->query['select'] ?? null;
        if (!is_array($select)) {
            $select = (array)$select;
        }

        return [
            $this->manipulate ?: 'SELECT', implode(',', $select ?: ['*'])
        ];
    }

    public function query(): static
    {
        $this->resultSet = null;

        $parameters = array_merge($this->parameters, []);
        $paramIndex = 0;
        $paramPrefix = 'P_';

        $sql = [
            ...$this->buildSelect(),
            ...$this->buildFrom(),
            ...$this->buildJoin(),
            ...$this->buildWhere($parameters, $paramIndex, $paramPrefix),
            ...$this->buildGroupBy($parameters, $paramIndex, $paramPrefix),
            ...$this->buildOrderBy(),
            ...$this->buildLimitOffset(),
        ];

        if (!$db = $this->connection) {
            if (!$connection = Db::connection($this)) {
                throw SystemException::of('No connection is configured for the entity %s.', static::class);
            }

            $db = $this->connection = Db::of($connection);
        }

        $this->resultSet = $db->query(implode(' ', $sql), $parameters)
            ->mode(Db::FETCH_DATA)
            ->callback($this->callback ?? $this->getClass())
            ->alias($this->primaryTableAlias ?: $this->getTable())
            ->cache($this->toCacheResultSet);

        return $this;
    }

    public function get(array $criteria = null, array $orderBy = null): static
    {
        return $this->getBy($criteria, $orderBy)->query()->object();
    }

    public function getBy(array $criteria = null, array $orderBy = null): static
    {
        foreach ($criteria ?? [] as $column => $value) {
            $this->and($column, $value);
        }

        foreach ($orderBy ?? [] as $column => $desc) {
            if (is_int($column)) {
                $this->orderBy($desc, false);
            } else {
                $this->orderBy($column, ($desc === true) || (strtolower((string)$desc === 'true')));
            }
        }

        return $this->query()->object();
    }

    public function first(array $criteria = null): ?static
    {
        if ($criteria || !$this->resultSet) {
            foreach ($criteria ?? [] as $column => $value) {
                $this->and($column, $value);
            }

            $this->query();
        }

        return $this->object();
    }

    public function firstOrFail(array $criteria = null): static
    {
        return $this->first($criteria) ?? throw new NotFoundException;
    }

    public function bind(string $name, mixed $value): static
    {
        $this->parameters[$name] = $value;
        return $this;
    }

    public function paginate(): ?array
    {
        if (($limit = intval($this->query['limit'] ?? null)) > 0) {
            $offset = max(0, intval($this->query['offset']));
            $page = ceil($offset / $limit) + 1;

            $total = $this->count();

            return compact('total', 'limit', 'offset', 'page');
        }

        return null;
    }

    public function exists(): bool
    {
        $clone = clone $this;
        $clone->query['select'] = "1";
        $clone->query['limit'] = 1;
        $clone->query['offset'] = 0;
        unset($clone->query['orderBy']);
        return $clone->query()->exists();
    }

    public function count(string|Stringable $column = null): int
    {
        $clone = clone $this;

        $column = $column ?: '*';
        $clone->query['select'] = "COUNT($column)";
        unset($clone->query['orderBy']);
        unset($clone->query['limit']);
        unset($clone->query['offset']);

        return $clone->query()->fetchColumn() ?? 0;
    }

    public function slice(int $limit, int $page = null): DbResultSet
    {
        $clone = clone $this;
        $clone->limit($limit, $page ?? 1);
        return $clone->query();
    }

    public function chunk(int $limit): iterable
    {
        $page = 1;
        $limit = max(1, $limit ?: 10);

        do {
            $count = 0;

            foreach ($this->slice($limit, $page++) as $name => $value) {
                yield $name => $value;

                $count++;
            }

        } while ($count >= $limit);
    }

    public function command(string $command, string|Stringable ...$columns): static
    {
        $clone = clone $this;
        $clone->select(sprintf('%s(%s)', $command, implode(',', $columns)));
        return $clone->query();
    }

    public function getIterator(): Traversable
    {
        $this->query();
        return $this->resultSet;
    }

    public function alias(string $alias = null): static
    {
        $this->primaryTableAlias = $alias;
        return $this;
    }

    public function aliasName(): string
    {
        return $this->primaryTableAlias ?? '';
    }

    /**
     * @param mixed|null $callback
     */
    public function callback(string|callable $callback = null, int $mode = null): static
    {
        $this->callback = $callback;
        if (func_num_args() > 1) {
            $this->mode = $mode;
        }

        return $this;
    }

//    public function cacheResultSet(bool $toCacheResultSet): static
//    {
//        $this->toCacheResultSet = $toCacheResultSet;
//        return $this;
//    }

//    protected function hasOne(string|RepositoryInterface $repository, string $localKey, string $foreignKey = null, callable $callback = null): mixed
//    {
//        if (isset($this->relationObjects['hasOne'][$localKey])) {
//            return $this->relationObjects['hasOne'][$localKey] ?: null;
//        }
//
//        $value = $this->__get($localKey);
//        if (empty($value)) {
//            return null;
//        }
//
//        $foreignKey ??= call_user_func([$repository, 'column_primary_key']) ?? 'id';
//        if ($instance = call_user_func([$repository, 'readonly'])) {
//            $instance->and($foreignKey, $value);
//            if ($callback) {
//                call_user_func($callback, $instance);
//            }
//        }
//
//        return $this->relationObjects['hasOne'][$localKey] = $instance?->first() ?? false;
//    }
//
//    protected function hasOneSet(string|RepositoryInterface $repository, string $localKey, array|object $value = null, string $foreignKey = null): void
//    {
//        if ($value === null) {
//            $this->__set($localKey, 0);
//            unset($this->relationObjects['hasOne'][$localKey]);
//            return;
//        }
//
//        if (is_array($value)) {
//            $value = call_user_func([$repository, 'from'], $value);
//        }
//
//        $foreignKey ??= call_user_func([$repository, 'column_primary_key']) ?? 'id';
//        $this->__set($localKey, $value->__get($foreignKey));
//        $this->relationObjects['hasOne'][$localKey] = $value;
//    }
//
//    protected function belongsTo(string|RepositoryInterface $repository, string $foreignKey, string $localKey = null, callable $callback = null): mixed
//    {
//        $id = $this->__get($localKey ?? static::column_primary_key());
//        if (!$id) {
//            return null;
//        }
//
//        $class = call_user_func([$repository, 'entity_class']);
//
//        if (isset($this->relationObjects['belongsTo'][$class][$foreignKey])) {
//            return $this->relationObjects['belongsTo'][$class][$foreignKey] ?: null;
//        }
//
//        if ($instance = call_user_func([$repository, 'findBy'], [$foreignKey => $id])) {
//            if ($callback) {
//                call_user_func($callback, $instance);
//            }
//        }
//
//        return $this->relationObjects['belongsTo'][$class][$foreignKey] = $instance?->first();
//    }
//
//    public function through(string|Extendable $relation, string $relationAlias, string $theOtherAliasOfRelation = null): static
//    {
//        if (!is_subclass_of($relation, Extendable::class, true)
//            || !is_subclass_of($relation, EntityInterface::class, true)) {
//            throw ValidationException::of('The `%s` is not a valid Relation.', is_string($relation) ? $relation : $relation::class);
//        }
//
//        if (!($relationName = call_user_func([$relation, 'entity_name']))) {
//            throw SystemException::of('No table name set to the relation class %s.',
//                is_string($relation) ? $relation : $relation::class
//            );
//        }
//
//        $localKey = null;
//        $theOtherName = null;
//        $theOtherClass = null;
//        $theOtherColumn = null;
//
//        if ($theOtherAliasOfRelation && !is_subclass_of($class, Relation::class, true)) {
//            $theOtherAliasOfRelation = null;
//        }
//
//        if ($primaryClass = call_user_func([$relation, 'relation_primary_class'])) {
//            if (is_subclass_of($this, $primaryClass)) {
//                $localKey = call_user_func([$relation, 'relation_primary_column']);
//                if ($theOtherAliasOfRelation) {
//                    $theOtherClass = call_user_func([$relation, 'relation_secondary_class']);
//                    $theOtherColumn = call_user_func([$relation, 'relation_secondary_column']);
//                }
//            }
//        }
//
//        if (!$localKey && ($secondaryClass = call_user_func([$relation, 'relation_secondary_class']))) {
//            if (is_subclass_of($this, $secondaryClass)) {
//                $localKey = call_user_func([$relation, 'relation_secondary_column']);
//                if ($theOtherAliasOfRelation) {
//                    $theOtherClass = $primaryClass;
//                    $theOtherColumn = call_user_func([$relation, 'relation_primary_column']);
//                }
//            }
//        }
//
//        if (!$localKey) {
//            throw SystemException::of('Unable to determine the relation between %s and %s.',
//                static::class,
//                is_string($relation) ? $relation : $relation::class
//            );
//        }
//
//        if ($theOtherAliasOfRelation) {
//            if (!$theOtherClass || !$theOtherColumn) {
//                throw SystemException::of('The other entity in the relation of %s with %s is not fully configured.',
//                    is_string($relation) ? $relation : $relation::class,
//                    static::class
//                );
//            }
//
//            $theOtherName = call_user_func([$theOtherClass, 'entity_name']);
//            if (!$theOtherName) {
//                throw SystemException::of('The table name of entity %s is not configured.', $theOtherClass);
//            }
//        } else {
//            $theOtherClass = null;
//        }
//
//        if ($alias = $this->aliasName()) {
//            $alias .= '.';
//        }
//
//        $repository = $this->select($relationAlias . '.*', $alias . '*')
//            ->innerJoin(
//                $relationName . ' AS ' . $relationAlias,
//                $relationAlias . '.' . $localKey,
//                $alias . 'id'
//            );
//
//        if ($theOtherAliasOfRelation) {
//            $theOtherAliasOfRelation = trim($theOtherAliasOfRelation, '.');
//
//            $repository->select($relationAlias . '.*', $theOtherAliasOfRelation . '.*', $alias . '*')
//                ->innerJoin(
//                    $theOtherName . ' AS ' . $theOtherAliasOfRelation,
//                    $relationAlias . '.' . $theOtherColumn,
//                    $theOtherAliasOfRelation . '.id'
//                );
//        }
//
//        return $repository;
//    }
//
//    protected function hasMany(string|RepositoryInterface $repository, string $foreignKey = null): RepositoryInterface
//    {
//        if (!is_subclass_of($repository, RepositoryInterface::class)) {
//            throw SystemException::of('The first argument must be a RepositoryInterface');
//        }
//
//        if (!$foreignKey) {
//            $foreignKey = call_user_func([$repository, 'relation_primary_column']);
//            if (!$foreignKey) {
//                throw SystemException::of(
//                    'The Relation %s has not set the primary column.',
//                    is_string($repository) ? $repository : $repository::class
//                );
//            }
//        }
//
//        return (($repository instanceof RepositoryInterface) ? $repository : $repository::readonly())
//            ->where($foreignKey, '=', $this->getId());
//    }
//
//    protected function hasManyThrough(string|RelationInterface $relation, string $alias = null, string $relationAlias = null): RepositoryInterface
//    {
//        $primaryClass = call_user_func([$relation, 'relation_primary_class']);
//        if (is_subclass_of($this, $primaryClass, true)) {
//            $field = call_user_func([$relation, 'relation_primary_column']);
//            $theClass = call_user_func([$relation, 'relation_secondary_class']);
//            $localKey = call_user_func([$relation, 'relation_secondary_column']);
//        } else {
//            $field = call_user_func([$relation, 'relation_secondary_column']);
//            $theClass = $primaryClass;
//            $localKey = call_user_func([$relation, 'relation_primary_column']);
//        }
//
//        if (!$theClass || !is_subclass_of($theClass, RepositoryInterface::class, true)) {
//            throw SystemException::of(
//                'The relation %s is incomplete.',
//                is_string($relation) ? $relation : $relation::class
//            );
//        }
//        $repository = $theClass::readonly();
//        if ($alias) {
//            $repository->alias($alias);
//        } else {
//            $alias = call_user_func([$theClass, 'entity_name']);
//        }
//
//        $relationAlias = $relationAlias ?: (($alias === 'R') ? 'RR' : 'R');
//
//        return $repository->innerJoin(
//            call_user_func([$relation, 'entity_name']) . ' AS ' . $relationAlias,
//            "$relationAlias.$localKey",
//            $alias . '.id')
//            ->where($relationAlias . '.' . $field, '=', $this->getId())
//            ->select("$relationAlias.*", "$alias.*");
//    }
}