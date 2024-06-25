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
use Autumn\Database\Interfaces\RelationInterface;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Exceptions\SystemException;
use Autumn\Exceptions\ValidationException;
use Stringable;
use Traversable;

trait RepositoryTrait
{
    #[Transient]
    private string $modelClass = '';

    #[Transient]
    private ?string $modelTable = null;

    #[Transient]
    private string $primaryTableAlias = '';

    #[Transient]
    private array $query = [];

    #[Transient]
    private array $parameters = [];

    #[Transient]
    private int $paramIndex = 0;

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

    private array $context = [];

    public function __construct(string|EntityInterface $entity, array $context = null, DbConnection $connection = null)
    {
        $entityClass = is_string($entity) ? $entity : $entity::class;
        if (!is_subclass_of($entityClass, EntityInterface::class)) {
            throw ValidationException::of('Invalid entity: `%s`', $entityClass);
        }

        $this->connection = $connection;
        $this->modelClass = $entityClass;

        if ($context) {
            $this->prepareFromContext($context);
        }
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    public function prepareFromContext(array $context): static
    {
        if ($value = (string)($context['alias'] ?? null)) {
            $this->primaryTableAlias = $value;
        }

        // do more

        $this->context = $context;
        return $this;
    }

    public function reset(): static
    {
        $this->query = [];
        $this->parameters = [];
        $this->paramIndex = 0;
        $this->manipulate = 'SELECT';
        $this->primaryTableAlias = '';
        $this->resultSet = null;

        return $this;
    }

    /**
     * @return string
     */
    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    public function getModelTable(): string
    {
        return $this->modelTable ??= Db::entity_name($this->getModelClass());
    }

    public function getLimitMax(): int
    {
        return $this->context['limit_default'] ?? env('QUERY_LIMIT_MAX') ?? Db::LIMIT_MAX;
    }

    public function alias(string $alias = null): static
    {
        $this->primaryTableAlias = $alias ?? '';
        return $this;
    }

    public function aliasName(): string
    {
        return $this->primaryTableAlias;
    }

    public function bindParam(string $name, mixed $value): static
    {
        $this->parameters[$name] = $value;
        return $this;
    }

    public function bindValue(mixed $value): string
    {
        do {
            $paramName = Db::PARAMETER_PREFIX . $this->paramIndex++;
        } while (array_key_exists($paramName, $this->parameters));

        $this->parameters[$paramName] = $value;
        return $paramName;
    }

    public function getIterator(): Traversable
    {
        return $this->query();
    }

    public function select(string|Stringable ...$columns): static
    {
        $this->resultSet = null;
        $this->manipulate = 'SELECT';
        $this->query['select'] = $columns; // = ['select' => $columns];
        return $this;
    }

    public function where(Stringable|string $condition, string $operator = null, mixed $value = null): static
    {
        $this->query['where'][] = ['AND', $condition, $operator, $value];
        return $this;
    }

    public function orWhere(Stringable|string $condition, string $operator = null, mixed $value = null): static
    {
        $this->query['where'][] = ['OR', $condition, $operator, $value];
        return $this;
    }

    public function whereNot(Stringable|string $condition, string $operator = null, mixed $value = null): static
    {
        $this->query['where'][] = ['AND NOT', $condition, $operator, $value];
        return $this;
    }

    public function orWhereNot(Stringable|string $condition, string $operator = null, mixed $value = null): static
    {
        $this->query['where'][] = ['OR NOT', $condition, $operator, $value];
        return $this;
    }

    public function and(Stringable|string $condition, mixed $value = null): static
    {
        return $this->where($condition, null, $value);
    }

    public function or(Stringable|string $condition, mixed $value = null): static
    {
        return $this->orWhere($condition, null, $value);
    }

    public function not(Stringable|string $condition, mixed $value = null): static
    {
        return $this->whereNot($condition, null, $value);
    }

    public function orNot(Stringable|string $condition, mixed $value = null): static
    {
        return $this->orWhereNot($condition, null, $value);
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
        if ($limit && ($limit > $this->getLimitMax())) {
            throw ValidationException::of('The limit of a query must not be larger than %s.', $this->getLimitMax());
        }

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
        $where = [];

        foreach ($conditions as [$and, $condition, $operator, $value]) {

            if (empty($where)) {
                $and = str_contains($and, 'NOT') ? 'NOT' : '';
            } else {
                $and = trim($and) . ' ';
            }

            if ($value === null && $operator === null) {
                $where[] = $and . $condition;
            } elseif ($value === null) {
                $where[] = "$and$condition $operator NULL";
            } else {
                $param = $paramPrefix . $paramIndex++;
                $parameters[$param] = $value;
                if (!$operator) {
                    $operator = is_array($value) ? 'IN' : '=';
                }
                $where[] = "$and($condition $operator :$param)";
            }
        }

        return $where ? implode(' ', $where) : '';
    }

    protected function buildFrom(): array
    {
        $sql = null;

        if ($this->primaryTableAlias) {
            $sql[] = $this->getModelTable() . ' AS ' . $this->primaryTableAlias;
        } else {
            $sql[] = $this->getModelTable();
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

    public function query(): DbResultSet
    {
        $this->resultSet = null;

        $parameters = array_merge($this->parameters, []);
        $paramIndex = $this->paramIndex;
        $paramPrefix = Db::PARAMETER_PREFIX;

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
            if (!$connection = Db::entity_connection_name($this)) {
                throw SystemException::of('No connection is configured for the entity %s.', static::class);
            }

            $db = $this->connection = Db::of($connection);
        }

        return $this->resultSet = $db
            ->query(implode(' ', $sql), $parameters)
            ->mode($this->mode ?? Db::FETCH_DATA)
            ->callback($this->callback ?? $this->getModelClass())
            ->alias($this->primaryTableAlias ?: $this->getModelTable())
            ->cache($this->toCacheResultSet);
    }

    public function aggregate(string $command, string|Stringable ...$columns): static
    {
        $column = implode($columns) ?: '*';

        $clone = clone $this;
        $clone->query['select'] = "$column($column)";
        return $clone;
    }

    public function count(Stringable|string $column = null, bool $distinct = null): int
    {
        $column = ($distinct ? 'DISTINCT ' : '') . ($column ?: '*');
        $clone = $this->aggregate('COUNT', $column);
        unset($clone->query['orderBy']);
        unset($clone->query['limit']);
        unset($clone->query['offset']);
        return $clone->query()->fetchColumn() ?? 0;
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

    public function paginate(): ?array
    {
        if (($limit = intval($this->query['limit'] ?? null)) > 0) {
            $offset = max(0, intval($this->query['offset'] ?? 0));
            $page = ceil($offset / $limit) + 1;

            $total = $this->count();

            return compact('total', 'limit', 'offset', 'page');
        }

        return null;
    }

    public function callback(callable|string $callback = null, int $mode = null): static
    {
        $this->callback = $callback;

        if (func_num_args() > 1) {
            $this->mode = $mode;
        }

        return $this;
    }

    public function through(string|RelationInterface $relationClass, string $relationAlias = 'R', string|Stringable ...$columns): RepositoryInterface
    {
        if (is_string($relationClass) && !is_subclass_of($relationClass, RelationInterface::class)) {
            throw ValidationException::of('Invalid relation class: `%s`', $relationClass);
        }

        $thisModelClass = $this->getModelClass();
        $relationTable = Db::entity_name($relationClass);

        $this->alias($alias = $this->aliasName() ?: $this->getModelTable());

        $relationPrimaryClass = $relationClass::relation_primary_class();
        $relationSecondaryClass = $relationClass::relation_secondary_class();

        if (is_subclass_of($thisModelClass, $relationPrimaryClass)) {
            $foreignKey = $relationClass::relation_primary_column();
        } elseif (is_subclass_of($thisModelClass, $relationSecondaryClass)) {
            $foreignKey = $relationClass::relation_secondary_column();
        } else {
            throw ValidationException::of(
                'The entity `%s` is not connected with this entity `%s`.',
                $relationClass::class,
                $thisModelClass
            );
        }

        if (empty($columns)) {
            $select = $this->query['select'] ?? null;
            if (empty($select)) {
                $this->query['select'] = [$relationAlias . '.*', $alias . '.*'];
            }
        } else {
            $this->query['select'] = $columns;
        }

        return $this->innerJoin(
            $relationTable . ' AS ' . $relationAlias,
            $alias . '.' . $this->getModelPrimaryKey(),
            $relationAlias . '.' . $foreignKey
        );
    }

    public function removeWhere(string|Stringable $condition, string $operator = null, mixed $value = null): static
    {
        foreach ($this->query['where'] ?? [] as $offset => $where) {
            if (($where[1] ?? null) === $condition) {
                if (($where[2] ?? null) === $operator) {
                    if (($where[3] ?? null) === $value) {
                        unset($this->query['where'][$offset]);
                    }
                }
            }
        }

        return $this;
    }

    public function whereIfNotSet(string|Stringable $condition, string $operator = null, mixed $value = null): static
    {
        foreach ($this->query['where'] ?? [] as $where) {
            if (($where[1] ?? null) === $condition) {
                if (($where[2] ?? null) === $operator) {
                    if (($where[3] ?? null) === $value) {
                        return $this;
                    }
                }
            }
        }

        return $this->where($condition, $operator, $value);
    }

    public function orWhereIfNotSet(string|Stringable $condition, string $operator = null, mixed $value = null): static
    {
        foreach ($this->query['where'] ?? [] as $where) {
            if (($where[1] ?? null) === $condition) {
                if (($where[2] ?? null) === $operator) {
                    if (($where[3] ?? null) === $value) {
                        return $this;
                    }
                }
            }
        }

        return $this->orWhere($condition, $operator, $value);
    }
}