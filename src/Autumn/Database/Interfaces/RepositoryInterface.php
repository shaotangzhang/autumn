<?php
/**
 * Autumn PHP Framework
 *
 * Date:        8/05/2024
 */

namespace Autumn\Database\Interfaces;

use Autumn\Database\DbResultSet;
use Autumn\Exceptions\ValidationException;
use Stringable;

/**
 * Interface RepositoryInterface
 *
 * Represents a repository interface for database interaction.
 */
interface RepositoryInterface extends \IteratorAggregate
{
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
     * Specify the columns to be selected in the query.
     *
     * @param string|Stringable ...$columns The columns to select.
     * @return static
     */
    public function select(string|Stringable ...$columns): static;

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
     * Add a "OR WHERE" condition to the query.
     *
     * @param string|Stringable $condition The condition to add.
     * @param string|null $operator The operator for the condition.
     * @param mixed $value The value to compare.
     * @return static
     */
    public function orWhere(string|Stringable $condition, string $operator = null, mixed $value = null): static;

    /**
     * Add a "AND NOT" condition to the query.
     *
     * @param string|Stringable $condition The condition to add.
     * @param string|null $operator The operator for the condition.
     * @param mixed $value The value to compare.
     * @return static
     */
    public function whereNot(string|Stringable $condition, string $operator = null, mixed $value = null): static;

    /**
     * Add a "OR NOT" condition to the query.
     *
     * @param string|Stringable $condition The condition to add.
     * @param string|null $operator The operator for the condition.
     * @param mixed $value The value to compare.
     * @return static
     */
    public function orWhereNot(string|Stringable $condition, string $operator = null, mixed $value = null): static;

    /**
     * Add an "AND" condition to the query.
     *
     * @param string|Stringable $condition The condition to add.
     * @param mixed $value The value to compare.
     * @return static
     */
    public function and(string|Stringable $condition, mixed $value = null): static;

    /**
     * Add an "OR" condition to the query.
     *
     * @param string|Stringable $condition The condition to add.
     * @param mixed $value The value to compare.
     * @return static
     */
    public function or(string|Stringable $condition, mixed $value = null): static;

    /**
     * Add an "AND NOT" condition to the query.
     *
     * @param string|Stringable $condition The condition to add.
     * @param mixed $value The value to compare.
     * @return static
     */
    public function not(string|Stringable $condition, mixed $value = null): static;

    /**
     * Add an "OR NOT" condition to the query.
     *
     * @param string|Stringable $condition The condition to add.
     * @param mixed $value The value to compare.
     * @return static
     */
    public function orNot(string|Stringable $condition, mixed $value = null): static;

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
     * Set the offset for the query.
     *
     * @param int|null $offset The number of rows to skip.
     * @return static
     */
    public function offset(int $offset = null): static;

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
     * @return DbResultSet The result set of the query.
     */
    public function query(): DbResultSet;

    /**
     * Execute a specific command with optional parameters.
     *
     * @param string $command The command to execute.
     * @param string|Stringable ...$columns The parameters for the command.
     * @return static
     */
    public function aggregate(string $command, string|Stringable ...$columns): static;

    /**
     * Count the number of rows in the query results.
     *
     * @param string|Stringable|null $column The column to count.
     * @param bool $distinct
     * @return int The count of rows.
     */
    public function count(Stringable|string $column = null, bool $distinct = null): int;

    /**
     * Check if any record exists under the configured condition(s).
     *
     * @return bool Return TRUE if at least one record exists, or otherwise FALSE.
     */
    public function exists(): bool;

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
    public function bindParam(string $name, mixed $value): static;

    /**
     * Bind a value to the parameters and returns the autogenerated parameter name.
     *
     * @param mixed $value
     * @return string
     */
    public function bindValue(mixed $value): string;

    public function callback(string|callable $callback = null, int $mode = null): static;

//    /**
//     * Create an inner join with a related entity through a specified relation class.
//     *
//     * @param string|RelationInterface $relationClass The relation class or its name.
//     * @param string $relationAlias Alias for the related entity table in the SQL query (default: 'R').
//     * @param string|Stringable ...$columns Optional columns to select from the related entity.
//     * @return RepositoryInterface The repository instance with the inner join applied.
//     * @throws ValidationException If the provided relation class is invalid or not a subclass of RelationInterface,
//     *                            or if the current entity is not connected with the specified relation class.
//     */
//    public function through(string|RelationInterface $relationClass, string $relationAlias = 'R', string|Stringable ...$columns): RepositoryInterface;

}
