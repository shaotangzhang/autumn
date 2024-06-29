<?php

namespace Autumn\Database\Traits;

use Autumn\Attributes\Transient;
use Autumn\Database\Db;
use Autumn\Database\DbConnection;
use Autumn\Database\DbResultSet;
use Autumn\Database\Interfaces\EntityInterface;
use Autumn\Database\Interfaces\EntityManagerInterface;
use Autumn\Exceptions\SystemException;
use Autumn\Exceptions\ValidationException;
use Traversable;

trait RepositoryTrait
{
    use RepositoryContextPreparationTrait;

    #[Transient]
    private readonly string $modelClass;

    #[Transient]
    private ?DbConnection $connection;

    #[Transient]
    private bool $readOnly = false;

    #[Transient]
    private array $context;

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
    private ?DbResultSet $resultSet = null;

    #[Transient]
    private string|array|null $modelPrimaryKey = null;

    #[Transient]
    private ?array $modelPrimaryKeys = null;

    /**
     * Creates a repository instance for the specified model class and optional context and database connection.
     *
     * @param string $modelClass The class name of the entity model.
     * @param array|null $context Optional. Context array containing settings and criteria.
     * @param DbConnection|null $connection Optional. Database connection instance.
     * @return static A new instance of the repository configured with the provided parameters.
     * @throws ValidationException If the provided model class does not implement EntityInterface.
     */
    public static function of(string $modelClass, array $context = null, DbConnection $connection = null): static
    {
        if (!is_subclass_of($modelClass, EntityInterface::class)) {
            throw ValidationException::of('Invalid entity: %s', $modelClass);
        }

        if (is_subclass_of($modelClass, EntityManagerInterface::class)) {
            return $modelClass::repository($context, $connection);
        }

        $instance = new static;

        $instance->modelClass = $modelClass;
        $instance->connection = $connection;
        $instance->readOnly = $connection === null;

        $instance->reset();
        if ($context) {
            $instance->__prepare_from_context__($context);
        }

        return $instance;
    }

    /**
     * Resets the query builder to its initial state.
     *
     * @return static The repository instance after resetting the query builder.
     */
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
     * Retrieves the current parameters set for the query.
     *
     * @return array The array of parameters set for the query.
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * Executes the built query and returns the result set.
     *
     * @return DbResultSet The result set obtained from executing the query.
     * @throws SystemException If no database connection is configured.
     */
    public function query(): DbResultSet
    {
        if (!$db = $this->__connection__()) {
            throw SystemException::of(
                'No connection is configured for the entity %s.',
                $this->__model_class__()
            );
        }

        $this->resultSet = null;

        $parametersBeforeQuery = $this->parameters;

        $sql = [
            ...$this->__build_select__(),
            ...$this->__build_from__(),
            ...$this->__build_joins__(),
            ...$this->__build_where__(),
            ...$this->__build_group_by__(),
            ...$this->__build_order_by__(),
            ...$this->__build_limit_offset__(),
        ];

        $result = $this->resultSet = $db
            ->query(implode(' ', $sql), $this->parameters)
            ->mode($this->mode ?? Db::FETCH_DATA)
            ->callback($this->callback ?? $this->__model_class__())
            ->alias($this->primaryTableAlias ?: $this->__model_table__())
            ->cache($this->toCacheResultSet);

        $this->parameters = $parametersBeforeQuery;
        return $result;
    }

    /**
     * Executes an aggregate function (e.g., COUNT, SUM) on the specified columns.
     *
     * @param string $command The aggregate function command (e.g., 'COUNT', 'SUM').
     * @param string|\Stringable ...$columns The columns to perform the aggregate function on.
     * @return static A clone of the repository instance with the aggregate function applied.
     */
    public function aggregate(string $command, string|\Stringable ...$columns): static
    {
        $column = implode($columns) ?: '*';

        $clone = clone $this;
        $clone->query['select'] = "$command($column)";
        return $clone;
    }

    public function count(string|\Stringable $column = null, bool $distinct = null): int
    {
        $column = ($distinct ? 'DISTINCT ' : '') . ($column ?: '*');
        $clone = $this->aggregate('COUNT', $column);
        $clone->query['orderBy'] = [];
        $clone->query['limit'] = null;
        $clone->query['offset'] = null;
        return $clone->query()->fetchColumn() ?? 0;
    }

    public function exists(): bool
    {
        $clone = clone $this;
        $clone->query['select'] = ["1"];
        $clone->query['limit'] = 1;
        $clone->query['offset'] = 0;
        $clone->query['orderBy'] = [];
        return $clone->query()->fetchColumn() !== null;
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

    public function getIterator(): Traversable
    {
        return $this->query();
    }

    public function callback(callable|string $callback = null, int $mode = null): static
    {
        $this->callback = $callback;

        if (func_num_args() > 1) {
            $this->mode = $mode;
        }

        return $this;
    }

    public function removeWhere(string|\Stringable $condition, string $operator = null, mixed $value = null): static
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

    public function whereIfNotSet(string|\Stringable $condition, string $operator = null, mixed $value = null): static
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

    public function orWhereIfNotSet(string|\Stringable $condition, string $operator = null, mixed $value = null): static
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

    public function alias(string $alias = null): static
    {
        $this->primaryTableAlias = $alias ? trim($alias, " \t\n\r\0\x0B.`") : $alias;
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

    public function select(string|\Stringable ...$columns): static
    {
        $this->resultSet = null;
        $this->manipulate = 'SELECT';
        $this->query['select'] = $columns; // = ['select' => $columns];
        return $this;
    }

    public function where(string|\Stringable $condition, string $operator = null, mixed $value = null): static
    {
        $this->query['where'][] = ['AND', $condition, $operator, $value];
        return $this;
    }

    public function orWhere(string|\Stringable $condition, string $operator = null, mixed $value = null): static
    {
        $this->query['where'][] = ['OR', $condition, $operator, $value];
        return $this;
    }

    public function whereNot(string|\Stringable $condition, string $operator = null, mixed $value = null): static
    {
        $this->query['where'][] = ['AND NOT', $condition, $operator, $value];
        return $this;
    }

    public function orWhereNot(string|\Stringable $condition, string $operator = null, mixed $value = null): static
    {
        $this->query['where'][] = ['OR NOT', $condition, $operator, $value];
        return $this;
    }

    public function and(string|\Stringable $condition, mixed $value = null): static
    {
        return $this->where($condition, null, $value);
    }

    public function or(string|\Stringable $condition, mixed $value = null): static
    {
        return $this->orWhere($condition, null, $value);
    }

    public function not(string|\Stringable $condition, mixed $value = null): static
    {
        return $this->whereNot($condition, null, $value);
    }

    public function orNot(string|\Stringable $condition, mixed $value = null): static
    {
        return $this->orWhereNot($condition, null, $value);
    }

    public function orderBy(string|\Stringable $column, bool $desc = null): static
    {
        $this->query['orderBy'][$column] = $desc;
        return $this;
    }

    public function groupBy(string|\Stringable ...$columns): static
    {
        foreach ($columns as $column) {
            $this->query['groupBy'][] = $column;
        }
        return $this;
    }

    public function having(string|\Stringable $condition, string $operator = null, mixed $value = null): static
    {
        $this->query['having'][] = [$condition, $operator, $value];
        return $this;
    }

    public function limit(int $limit = null, int $page = null): static
    {
        if ($limit && ($limit > $this->__limit_max__())) {
            throw ValidationException::of('The limit of a query must not be larger than %s.', $this->__limit_max__());
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

    public function join(string|\Stringable $table, string $type = null, string|\Stringable $localKey = null, string|\Stringable $foreignKey = null): static
    {
        $this->query['join'][] = [$type, $table, $localKey, $foreignKey];
        return $this;
    }

    public function innerJoin(string|\Stringable $table, string|\Stringable $localKey = null, string|\Stringable $foreignKey = null): static
    {
        return $this->join($table, 'INNER', $localKey, $foreignKey);
    }

    public function leftJoin(string|\Stringable $table, string|\Stringable $localKey = null, string|\Stringable $foreignKey = null): static
    {
        return $this->join($table, 'LEFT', $localKey, $foreignKey);
    }

    public function rightJoin(string|\Stringable $table, string|\Stringable $localKey = null, string|\Stringable $foreignKey = null): static
    {
        return $this->join($table, 'RIGHT', $localKey, $foreignKey);
    }

    public function outerJoin(string|\Stringable $table, string|\Stringable $localKey = null, string|\Stringable $foreignKey = null): static
    {
        return $this->join($table, 'OUTER', $localKey, $foreignKey);
    }

    protected function __affective_result__(): ?DbResultSet
    {
        return $this->resultSet ??= $this->query();
    }

    protected function __model_class__(): string
    {
        return $this->modelClass;
    }

    protected function __context__(): array
    {
        return $this->context ??= [];
    }

    protected function __connection__(): ?DbConnection
    {
        return $this->connection ??= Db::forEntity($this->modelClass);
    }

    protected function __readOnly__(): bool
    {
        return $this->readOnly;
    }

    protected function __model_table__(): string
    {
        return $this->modelTable ??= Db::entity_name($this->__model_class__());
    }

    protected function __model_primary_key__(): string|array|null
    {
        return $this->modelPrimaryKey ??= Db::entity_primary_key($this->__model_class__());
    }

    protected function __model_primary_keys__(): array
    {
        if ($this->modelPrimaryKeys === null) {
            $key = $this->__model_primary_key__() ?? [];
            $this->modelPrimaryKeys = is_string($key) ? [$key] : $key;
        }

        return $this->modelPrimaryKeys;
    }

    protected function __limit_max__(): int
    {
        return $this->context['limit_max'] ?? \env('QUERY_LIMIT_MAX') ?? Db::LIMIT_MAX;
    }

    protected function __limit_default__(): int
    {
        return $this->context['limit_default'] ?? \env('QUERY_LIMIT_DEFAULT') ?? Db::LIMIT_DEFAULT;
    }

    protected function __build_where_conditions__(array $conditions): string
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
                $param = $this->bindValue($value);
                if (!$operator) {
                    $operator = is_array($value) ? 'IN' : '=';
                }
                $where[] = "$and($condition $operator :$param)";
            }
        }

        return $where ? implode(' ', $where) : '';
    }

    protected function __build_from__(): array
    {
        $sql = null;

        if ($this->primaryTableAlias) {
            $sql[] = $this->__model_table__() . ' AS ' . $this->primaryTableAlias;
        } else {
            $sql[] = $this->__model_table__();
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

    protected function __build_joins__(): array
    {
        $sql = [];

        if (isset($this->query['join'])) {
            foreach ($this->query['join'] as [$type, $table, $localKey, $foreignKey]) {
                $sql[] = "$type JOIN $table ON $localKey = $foreignKey";
            }
        }

        return $sql;
    }

    protected function __build_where__(): array
    {
        $sql = [];

        if (isset($this->query['where'])) {
            $where = $this->__build_where_conditions__($this->query['where']);

            if ($where) {
                $sql[] = 'WHERE';
                $sql[] = $where;
            }
        }

        return $sql;
    }

    protected function __build_group_by__(): array
    {
        $sql = [];

        if (isset($this->query['groupBy'])) {
            $sql[] = 'GROUP BY';
            $sql[] = implode(',', $this->query['groupBy']);

            if (isset($this->query['having'])) {
                $having = $this->__build_where_conditions__($this->query['having']);

                if ($having) {
                    $sql[] = 'HAVING';
                    $sql[] = $having;
                }
            }
        }

        return $sql;
    }

    protected function __build_order_by__(): array
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

    protected function __build_limit_offset__(): array
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

    protected function __build_select__(): array
    {
        $select = $this->query['select'] ?? null;
        if (!is_array($select)) {
            $select = (array)$select;
        }

        return [
            $this->manipulate ?: 'SELECT', implode(',', $select ?: ['*'])
        ];
    }
}