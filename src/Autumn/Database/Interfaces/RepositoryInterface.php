<?php
/**
 * Autumn PHP Framework
 *
 * Date:        18/06/2024
 */

namespace Autumn\Database\Interfaces;

use Autumn\Database\DbConnection;
use Autumn\Database\DbResultSet;
use Stringable;

interface RepositoryInterface
{
    public function find(int|array $criteria = null): mixed;

    public function findBy(int|array $criteria): static;

    public function findOrFail(int|array $criteria, string $message = null, int $statusCode = null): static;

    /**
     * Specify the columns to be selected in the query.
     *
     * @param string|Stringable ...$columns The columns to select.
     * @return static
     */
    public function select(string|Stringable ...$columns): static;

    /**
     * Add an "AND" condition to the query.
     *
     * @param string|Stringable $condition The condition to add.
     * @param mixed $value The value to compare.
     * @return static
     */
    public function and(string|Stringable $condition, mixed $value = null): static;

    /**
     * Add a "WHERE" condition to the query.
     *
     * @param string|Stringable $condition The condition to add.
     * @param string|null $operator The operator for the condition.
     * @param mixed $value The value to compare.
     * @return static
     */
    public function where(string|Stringable $condition, string $operator = null, mixed $value = null): static;

    /**
     * Add an "ORDER BY" clause to the query.
     *
     * @param string|Stringable $column The column to order by.
     * @param bool|null $desc Whether to order in descending order.
     * @return static
     */
    public function orderBy(string|Stringable $column, bool $desc = null): static;

    /**
     * Add a "GROUP BY" clause to the query.
     *
     * @param string|Stringable ...$columns The columns to group by.
     * @return static
     */
    public function groupBy(string|Stringable ...$columns): static;

    /**
     * Add a "HAVING" clause to the query.
     *
     * @param string|Stringable $condition The condition to add.
     * @param string|null $operator The operator for the condition.
     * @param mixed $value The value to compare.
     * @return static
     */
    public function having(string|Stringable $condition, string $operator = null, mixed $value = null): static;

    /**
     * Set the limit and optional offset for the query.
     *
     * @param int|null $limit The maximum number of rows to return.
     * @param int|null $page The offset or page number.
     * @return static
     */
    public function limit(int $limit = null, int $page = null): static;

    /**
     * Add a "JOIN" clause to the query.
     *
     * @param string|Stringable $table The table to join.
     * @param string|null $type The type of join (e.g., INNER, LEFT, RIGHT).
     * @param string|Stringable|null $localKey The local key for the join.
     * @param string|Stringable|null $foreignKey The foreign key for the join.
     * @return static
     */
    public function join(string|Stringable $table, string $type = null, string|Stringable $localKey = null, string|Stringable $foreignKey = null): static;

    /**
     * Add a "INNER JOIN" clause to the query.
     *
     * @param string|Stringable $table The table to join.
     * @param string|Stringable|null $localKey The local key for the join.
     * @param string|Stringable|null $foreignKey The foreign key for the join.
     * @return static
     */
    public function innerJoin(string|Stringable $table, string|Stringable $localKey = null, string|Stringable $foreignKey = null): static;

    /**
     * Add a "LEFT JOIN" clause to the query.
     *
     * @param string|Stringable $table The table to join.
     * @param string|Stringable|null $localKey The local key for the join.
     * @param string|Stringable|null $foreignKey The foreign key for the join.
     * @return static
     */
    public function leftJoin(string|Stringable $table, string|Stringable $localKey = null, string|Stringable $foreignKey = null): static;

    /**
     * Add a "RIGHT JOIN" clause to the query.
     *
     * @param string|Stringable $table The table to join.
     * @param string|Stringable|null $localKey The local key for the join.
     * @param string|Stringable|null $foreignKey The foreign key for the join.
     * @return static
     */
    public function rightJoin(string|Stringable $table, string|Stringable $localKey = null, string|Stringable $foreignKey = null): static;

    /**
     * Add a "OUTER JOIN" clause to the query.
     *
     * @param string|Stringable $table The table to join.
     * @param string|Stringable|null $localKey The local key for the join.
     * @param string|Stringable|null $foreignKey The foreign key for the join.
     * @return static
     */
    public function outerJoin(string|Stringable $table, string|Stringable $localKey = null, string|Stringable $foreignKey = null): static;

    /**
     * Execute the query and return a result set.
     *
     * @return static
     */
    public function query(): static;

    /**
     * Execute a specific command with optional parameters.
     *
     * @param string $command The command to execute.
     * @param string|Stringable ...$columns The parameters for the command.
     * @return static The result set of the command.
     */
    public function command(string $command, string|Stringable ...$columns): static;

    /**
     * Slice the query results to limit the number of rows returned.
     *
     * @param int $limit The maximum number of rows to return.
     * @param int|null $page The offset or page number.
     * @return static The result set of the sliced query.
     */
    public function slice(int $limit, int $page = null): static;

    /**
     * Chunk the query results into iterable chunks.
     *
     * @param int $limit The maximum number of rows per chunk.
     * @return iterable The iterable chunks of the query results.
     */
    public function chunk(int $limit): iterable;

    /**
     * Count the number of rows in the query results.
     *
     * @param string|Stringable|null $column The column to count.
     * @return int The count of rows.
     */
    public function count(string|Stringable $column = null): int;

    /**
     * Check if any record exists under the configured condition(s).
     *
     * @return bool Return TRUE if at least one record exists, or otherwise FALSE.
     */
    public function exists(): bool;

    /**
     * Set the alias of the primary table
     *
     * @param string|null $alias
     * @return $this
     */
    public function alias(string $alias = null): static;

    /**
     * Return the alias of the primary table
     *
     * @return string
     */
    public function aliasName(): string;

    /**
     * Return the pagination data of the query results.
     *
     * @return array|null Return NULL if the query has not LIMIT setting
     */
    public function paginate(): ?array;

    /**
     * Bind a name-value pair to the parameters
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function bind(string $name, mixed $value): static;

    public function callback(string|callable $callback = null, int $mode = null): static;

    public function through(string|Extendable $relation, string $relationAlias, string $theOtherAliasOfRelation = null): static;
}