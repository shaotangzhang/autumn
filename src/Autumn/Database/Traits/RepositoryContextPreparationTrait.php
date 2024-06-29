<?php

namespace Autumn\Database\Traits;

use Autumn\Attributes\Transient;
use Autumn\Database\Models\Conditions;
use Autumn\Exceptions\ValidationException;

/**
 * @method static column_primary_key()
 */
trait RepositoryContextPreparationTrait
{
    #[Transient]
    protected ?array $defaultCriteria = null;

    #[Transient]
    protected ?array $defaultSorting = null;

    #[Transient]
    protected array $searchKeywordInColumns = [];

    #[Transient]
    protected bool $searchKeywordInBooleanMode = false;

    #[Transient]
    protected array $allowedSortingFields = [
        // ...columns
        // alias => column
        // group => [...columns]
    ];

    #[Transient]
    protected bool $ignoreInvalidSorting = true;

    #[Transient]
    protected bool $ignoreInvalidCriteria = false;

    /**
     * Execute a scoped method on the repository based on the provided name and context.
     *
     * @param string $name The name of the scoped method to execute.
     * @param array|null $context The context array containing settings and criteria.
     *
     * @return static|null The repository instance after executing the scoped method, or null if the method does not exist.
     */
    public static function scope(string $name, array $context = null): ?static
    {
        if (method_exists(static::class, $func = 'scope' . $name)) {
            return static::of($context)->$func();
        }

        return null;
    }

    /**
     * Prepare the repository based on the provided context.
     *
     * @param array $context The context array containing settings and criteria.
     *
     * @return static The current repository instance.
     */
    protected function __prepare_from_context__(array $context): static
    {
        if ($alias = $context['alias'] ?? $context['primaryAlias'] ?? null) {
            if ($alias = $this->alias($alias)->aliasName()) {
                $alias .= '.';
            }
        }

        if ($this->defaultCriteria) {
            $this->refineCriteria(['criteria' => $this->defaultCriteria], $alias);
        }

        foreach (array_keys($context) as $name) {
            if (method_exists($this, $func = 'refine' . $name)
                || method_exists($this, $func = 'scope' . $name)) {
                $this->$func($context, $alias);
            }
        }

        if ($this->defaultSorting && !isset($context['orderBy'])) {
            $this->refineOrderBy(['orderBy' => $this->defaultSorting], $alias);
        }

        return $this;
    }

    /**
     * Get the columns to search keywords in.
     *
     * @return array The columns to search keywords in.
     */
    public function __search_keyword_in_columns__(): array
    {
        return $this->searchKeywordInColumns;
    }

    /**
     * Whether to use boolean mode for keyword search.
     *
     * @return bool Whether to use boolean mode for keyword search.
     */
    public function __search_keyword_in_boolean_mode__(): bool
    {
        return $this->searchKeywordInBooleanMode;
    }

    /**
     * Get the default criteria to apply.
     *
     * @return array The default criteria.
     */
    protected function __default_criteria__(): array
    {
        return $this->defaultCriteria ?? [];
    }

    /**
     * Get the default sorting to apply.
     *
     * @return array The default sorting.
     */
    protected function __default_sorting__(): array
    {
        return $this->defaultSorting ?? [];
    }

    /**
     * Apply the given criteria to the repository query.
     *
     * @param array|null $criteria The criteria to apply.
     * @param string|null $primaryAlias The primary alias for the table.
     *
     * @return static The current repository instance.
     */
    public function applyCriteria(array $criteria = null, string $primaryAlias = null): static
    {
        foreach ($criteria as $column => $value) {
            if (is_string($column)) {
                if (!str_contains($column, '.')) {
                    $column = $primaryAlias . $column;
                }

                $this->and($column, $value);
            } else {
                $this->where(Conditions::of($value, 'OR'));
            }
        }

        return $this;
    }

    /**
     * Apply the given sorting to the repository query.
     *
     * @param string|array|null $sorting The sorting to apply.
     * @param string|null $primaryAlias The primary alias for the table.
     *
     * @return static The current repository instance.
     */
    public function applySorting(string|array $sorting = null, string $primaryAlias = null): static
    {
        return $this->refineOrderBy(['orderBy' => $sorting], $primaryAlias);
    }

    /**
     * Perform a search operation based on provided criteria and context.
     *
     * @param array|null $criteria The additional criteria to apply.
     * @param array|null $context The context array containing settings and criteria.
     *
     * @return static The current repository instance.
     */
    public function search(array $criteria = null, array $context = null): static
    {
        $criteria = array_merge($this->__default_criteria__(), $criteria ?? []);

        return static::of($this->__model_class__(), $context)->applyCriteria($criteria);
    }

    /**
     * Scope method: Apply a 'none' condition to the query if specified in the context.
     *
     * @param array|null $context The context array containing settings and criteria.
     * @param string|null $primaryAlias The primary alias for the table.
     *
     * @return static The current repository instance.
     */
    protected function scopeNone(array $context = null, string $primaryAlias = null): static
    {
        if ($context['none'] ?? null) {
            return $this->where('false');
        }

        return $this;
    }

    /**
     * Refine the criteria for the query based on the given context and primary alias.
     *
     * @param array|null $context The context array containing criteria information.
     * @param string|null $primaryAlias The primary alias for the table.
     *
     * @return static The current repository instance.
     */
    protected function refineCriteria(array $context = null, string $primaryAlias = null): static
    {
        if ($criteria = $context['criteria'] ?? []) {
            if (!$this->__ignore_invalid_criteria__()) {
                $message = 'Invalid criterion.';
            }
            return $this->__criterion__($criteria, $context, $primaryAlias, $message ?? null);
        }

        return $this;
    }

    /**
     * Determine if invalid criteria should be ignored.
     *
     * @return bool True if invalid criteria should be ignored, false otherwise.
     */
    protected function __ignore_invalid_criteria__(): bool
    {
        return $this->ignoreInvalidCriteria;
    }

    /**
     * Apply the criterion to the query based on its type and value.
     *
     * @param mixed $criteria The criterion to apply.
     * @param array|null $context The context array containing additional information.
     * @param string|null $primaryAlias The primary alias for the table.
     * @param string|null $messageOnError The error message if validation fails.
     *
     * @return static The current repository instance.
     *
     * @throws ValidationException If the criterion is invalid.
     */
    protected function __criterion__(mixed $criteria, array $context = null, string $primaryAlias = null, string $messageOnError = null): static
    {
        if ($this->__is_criteria_empty__($criteria) || $this->__is_criteria_true__($criteria)) {
            return $this;
        }

        if ($this->__is_criteria_false__($criteria)) {
            return $this->where('false');
        }

        if (is_int($criteria)) {
            return $this->__where_id__($criteria, $context, $primaryAlias);
        }

        if (is_string($criteria) && method_exists($this, $func = 'scope' . $criteria)) {
            return $this->$func($context, $primaryAlias);
        }

        if (!is_array($criteria)) {
            throw ValidationException::of($messageOnError);
        }

        return $this->__apply_criteria_array__($criteria, $context, $primaryAlias, $messageOnError);
    }

    /**
     * Check if the criterion is empty.
     *
     * @param mixed $criteria The criterion to check.
     *
     * @return bool True if the criterion is considered empty, false otherwise.
     */
    protected function __is_criteria_empty__(mixed $criteria): bool
    {
        return $criteria === null || $criteria === [] || $criteria === '';
    }

    /**
     * Check if the criterion evaluates to true.
     *
     * @param mixed $criteria The criterion to check.
     *
     * @return bool True if the criterion evaluates to true, false otherwise.
     */
    protected function __is_criteria_true__(mixed $criteria): bool
    {
        return $criteria === true || $criteria === 'true';
    }

    /**
     * Check if the criterion evaluates to false.
     *
     * @param mixed $criteria The criterion to check.
     *
     * @return bool True if the criterion evaluates to false, false otherwise.
     */
    protected function __is_criteria_false__(mixed $criteria): bool
    {
        return $criteria === false || $criteria === 'false';
    }

    /**
     * Apply an array of criteria to the query.
     *
     * @param array $criteria The criteria to apply.
     * @param array|null $context The context array containing additional information.
     * @param string|null $primaryAlias The primary alias for the table.
     * @param string|null $messageOnError The error message if validation fails.
     *
     * @return static The current repository instance.
     *
     * @throws ValidationException If a criterion in the array is invalid.
     */
    protected function __apply_criteria_array__(array $criteria, array $context = null, string $primaryAlias = null, string $messageOnError = null): static
    {
        $selected = $this->query['select'] ?? [];

        if ($messageOnError) {
            $messageOnError = rtrim($messageOnError, '.') . ' `%s`';
        }

        foreach ($criteria as $column => $value) {
            if (is_int($column)) {
                $this->__criterion__($value, $context, $primaryAlias);
                continue;
            }

            if (method_exists($this, $func = '__where_' . $column . '__')) {
                $this->$func($value, $context, $primaryAlias);
                continue;
            }

            if ($this->__is_column_selectable__($column, $selected, $primaryAlias)) {
                $this->and($column, $value);
                continue;
            }

            if ($messageOnError) {
                throw ValidationException::of($messageOnError, $column);
            }
        }

        return $this;
    }

    /**
     * Check if a column is selectable based on the selected columns and primary alias.
     *
     * @param string $column The column to check.
     * @param array $selected The selected columns.
     * @param string|null $primaryAlias The primary alias for the table.
     *
     * @return bool True if the column is selectable, false otherwise.
     */
    protected function __is_column_selectable__(string $column, array $selected, string $primaryAlias=null): bool
    {
        return empty($selected)
            || in_array($column, $selected)
            || in_array($primaryAlias . $column, $selected);
    }

    /**
     * Apply a where condition based on the ID column.
     *
     * @param int|array $value The value(s) of the ID column.
     * @param array|null $context The context array containing additional information.
     * @param string|null $primaryAlias The primary alias for the table.
     *
     * @return static The current repository instance.
     *
     * @throws ValidationException If the ID column is undefined or invalid.
     */
    protected function __where_id__(int|array $value, array $context = null, string $primaryAlias = null): static
    {
        $column = static::column_primary_key();
        if (!$column || is_array($column)) {
            if (!$this->__ignore_invalid_criteria__()) {
                throw ValidationException::of('ID column is undefined.');
            }
            return $this;
        }

        if (is_array($value)) {
            $value = array_unique(array_map('intval', $value));
            if (empty($value)) {
                if (!$this->__ignore_invalid_criteria__()) {
                    throw ValidationException::of('No ID in the value list.');
                }
                return $this;
            }
        }

        return $this->and($primaryAlias . $column, $value);
    }

    /**
     * Refine the page settings for pagination based on the given context and primary alias.
     *
     * @param array|null $context The context array containing page and limit information.
     * @param string|null $primaryAlias The primary alias for the table.
     *
     * @return static The current repository instance.
     */
    protected function refinePage(array $context = null, string $primaryAlias = null): static
    {
        if (!isset($context['limit']) && isset($context['limit_default'])) {
            return $this->refineLimit($context, $primaryAlias);
        }

        return $this;
    }

    /**
     * Refine the limit and page settings for pagination based on the given context and primary alias.
     *
     * @param array|null $context The context array containing limit, page, and offset information.
     * @param string|null $primaryAlias The primary alias for the table.
     *
     * @return static The current repository instance.
     */
    protected function refineLimit(array $context = null, string $primaryAlias = null): static
    {
        if ($limit = $context['limit'] ?? $context['limit_default'] ?? null) {
            $limit = intval($limit);
        }

        if ($page = $context['page'] ?? null) {
            $page = intval($page);
        }

        if (!$page && $limit) {
            $page = ceil(intval($context['offset'] ?? null) / $limit);
        }

        return $this->limit($limit, $page ?: null);
    }

    /**
     * Refine the search criteria based on the given context and primary alias.
     *
     * @param array|null $context The context array containing search keyword information.
     * @param string|null $primaryAlias The primary alias for the table.
     *
     * @return static The current repository instance.
     */
    protected function refineSearch(array $context = null, string $primaryAlias = null): static
    {
        if ($search = $context['search'] ?? null) {
            if (!is_string($search)) {
                $search = null;
            }
        }

        if (!$search) {
            return $this;
        }

        $columns = $this->__search_keyword_in_columns__();
        if (empty($columns)) {
            return $this;
        }

        $primaryAlias = $this->__primary_alias__($primaryAlias);

        if ($this->__search_keyword_in_boolean_mode__()) {
            $keyword = preg_replace('/\s+/', ' ', trim($search));
            $columnsString = $primaryAlias . implode(', ' . $primaryAlias, $columns);
            $this->where("MATCH($columnsString) AGAINST(:keyword IN BOOLEAN MODE)");
        } else {
            $keyword = preg_replace('/\s+/', '%', trim($search));

            $conditions = [];
            foreach ($columns as $column) {
                $conditions[] = "$primaryAlias$column LIKE :keyword";
            }
            $this->where(Conditions::of($conditions, 'OR'));
        }

        $this->bindParam('keyword', $keyword);
        return $this;
    }


    /**
     * Refine the order by clause based on the given context and primary alias.
     *
     * @param array|null $context The context array containing sorting information.
     * @param string|null $primaryAlias The primary alias for the table.
     *
     * @return static The current repository instance.
     */
    protected function refineOrderBy(array $context = null, string $primaryAlias = null): static
    {
        $primaryAlias = $this->__primary_alias__($primaryAlias);

        $sorting = [];

        if ($orderBy = $context['orderBy'] ?? null) {
            if (is_string($orderBy) || is_array($orderBy)) {
                $message = $this->__ignore_invalid_sorting__() ? 'Invalid sorting direction setting.' : null;
                $desc = $this->__parse_desc__($context['desc'] ?? null, $message);

                $message = $this->__ignore_invalid_sorting__() ? 'Invalid sorting column `%s`.' : null;
                $sorting = $this->__parse_orderBy__($orderBy, $desc, $message);
            }
        }

        foreach ($sorting as $column => $desc) {
            if ($primaryAlias && !str_contains($column, '.')) {
                $column = $primaryAlias . $column;
            }
            $this->orderBy($column, $desc);
        }

        return $this;
    }

    /**
     * Get the primary alias for the table.
     *
     * @param string|null $primaryAlias The primary alias for the table.
     *
     * @return string|null The primary alias.
     */
    protected function __primary_alias__(string $primaryAlias = null): ?string
    {
        if ($primaryAlias === null) {
            if ($primaryAlias = $this->aliasName()) {
                $primaryAlias .= '.';
            }
        }

        return $primaryAlias;
    }

    /**
     * Get the allowed sorting fields.
     *
     * @return array The allowed sorting fields.
     */
    protected function __allowed_sorting_fields__(): array
    {
        return $this->allowedSortingFields;
    }

    /**
     * Determine if invalid sorting should be ignored.
     *
     * @return bool True if invalid sorting should be ignored, false otherwise.
     */
    protected function __ignore_invalid_sorting__(): bool
    {
        return $this->ignoreInvalidSorting;
    }

    /**
     * Parse the descending sorting value.
     *
     * @param mixed $desc The descending value to parse.
     * @param string|null $messageOnError The error message if parsing fails.
     *
     * @return bool|null True if descending, false if ascending, or null if not set.
     *
     * @throws ValidationException If the descending value is invalid.
     */
    private function __parse_desc__(mixed $desc = null, string $messageOnError = null): ?bool
    {
        if (is_bool($desc) || is_null($desc)) {
            return $desc;
        }

        if (is_string($desc)) {
            if (strcasecmp($desc, 'DESC') === 0) {
                return true;
            }

            if (strcasecmp($desc, 'ASC') === 0) {
                return false;
            }

            if (!trim($desc)) {
                return false;
            }

            if ($messageOnError) {
                $messageOnError .= ' `%s` is given.';
            }
        }

        $result = filter_var($desc, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        if (is_null($result) && $messageOnError) {
            throw ValidationException::of($messageOnError, $desc);
        }
        return $result;
    }

    /**
     * Parse the sorting string into an array.
     *
     * @param string $orderBy The sorting string to parse.
     * @param string|null $messageOnError The error message if parsing fails.
     *
     * @return array The parsed sorting array.
     *
     * @throws ValidationException If the sorting string is invalid.
     */
    private function __parse_sort__(string $orderBy, string $messageOnError = null): array
    {
        $sorting = [];
        foreach (preg_split('/\s*[,;|+]+\s*/', trim($orderBy)) as $part) {
            if (preg_match('/^(\w+(?:\.\w+)?)(?:\s+(ASC|DESC))?$/i', $part, $matches)) {
                $sorting[$matches[1]] = $matches[2] ?? false;
            } elseif ($messageOnError) {
                throw ValidationException::of($messageOnError, $part);
            }
        }
        return $sorting;
    }

    /**
     * Parse the order by clause.
     *
     * @param string|array $orderBy The order by clause to parse.
     * @param bool $desc True if the default sorting is descending, false otherwise.
     * @param string|null $messageOnError The error message if parsing fails.
     *
     * @return array The parsed order by array.
     *
     * @throws ValidationException If the order by clause is invalid.
     */
    private function __parse_orderBy__(string|array $orderBy, bool $desc = false, string $messageOnError = null): array
    {
        $allowedFields = $this->__allowed_sorting_fields__();
        if (empty($allowedFields)) {
            return [];
        }

        if (is_string($orderBy)) {
            $sorting = $this->__parse_sort__($orderBy, $messageOnError);
        } else {
            $sorting = $orderBy;
        }

        if (empty($sorting)) {
            return [];
        }

        foreach ($sorting as $name => $direction) {
            if (is_int($name)) {
                $name = $direction;
                $direction = null;
            }

            if (in_array($name, $allowedFields)) {
                $direct = $this->__parse_sort__($direction, $messageOnError) ?? false;
                $sorting[$name] = $desc ? !$direct : $direct;
                continue;
            }

            if (!isset($allowedFields[$name])) {
                if ($messageOnError) {
                    throw ValidationException::of($messageOnError, $name);
                }
                continue;
            }

            if (is_string($allowedFields[$name])) {
                $sorting[$allowedFields[$name]] = $desc;
            } else {
                foreach ($allowedFields[$name] as $column => $value) {
                    if (is_int($column)) {
                        $column = $value;
                        $value = false;
                    }

                    $direct = $this->__parse_sort__($value, $messageOnError) ?? false;
                    $sorting[$column] = $desc ? !$direct : $direct;
                }
            }
        }

        return $sorting;
    }
}