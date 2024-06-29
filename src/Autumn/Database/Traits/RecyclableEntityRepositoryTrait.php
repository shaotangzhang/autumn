<?php

namespace Autumn\Database\Traits;

/**
 * Trait RecyclableEntityRepositoryTrait
 *
 * Provides methods for handling "trashed" or soft-deleted entities in the repository.
 */
trait RecyclableEntityRepositoryTrait
{
    // use EntityRepositoryTrait;

    /**
     * Builds the condition to include only trashed (soft-deleted) entities.
     *
     * @param string $column The name of the column used for soft deletion.
     * @param string|null $alias Optional. The alias for the table.
     * @return string The SQL condition for including only trashed entities.
     */
    private function buildOnlyTrashedCondition(string $column, string $alias = null): string
    {
        return "($alias$column IS NOT NULL AND $alias$column <= CURRENT_TIMESTAMP())";
    }

    /**
     * Builds the condition to exclude trashed (soft-deleted) entities.
     *
     * @param string $column The name of the column used for soft deletion.
     * @param string|null $alias Optional. The alias for the table.
     * @return string The SQL condition for excluding trashed entities.
     */
    private function buildWithoutTrashedCondition(string $column, string $alias = null): string
    {
        return "($alias$column IS NULL OR $alias$column > CURRENT_TIMESTAMP())";
    }

    /**
     * Adds a condition to the query to include only non-trashed entities.
     *
     * @return static The repository instance with the condition applied.
     */
    public function scopeNormal(): static
    {
        return $this->withoutTrashed();
    }

    /**
     * Adds a condition to the query to include only trashed (soft-deleted) entities.
     *
     * @return static The repository instance with the condition applied.
     */
    public function scopeTrashed(): static
    {
        return $this->onlyTrashed();
    }

    /**
     * Modifies the query to include only trashed (soft-deleted) entities.
     *
     * @return static The repository instance with the condition applied.
     */
    public function onlyTrashed(): static
    {
        if ($column = static::column_deleted_at()) {
            $conditionToRemove = $this->buildWithoutTrashedCondition($column, $this->aliasName());
            $this->removeWhere($conditionToRemove);

            $condition = $this->buildOnlyTrashedCondition($column, $this->aliasName());
            return $this->whereIfNotSet($condition);
        }

        return $this;
    }

    /**
     * Modifies the query to exclude trashed (soft-deleted) entities.
     *
     * @return static The repository instance with the condition applied.
     */
    public function withoutTrashed(): static
    {
        if ($column = static::column_deleted_at()) {
            $conditionToRemove = $this->buildOnlyTrashedCondition($column, $this->aliasName());
            $this->removeWhere($conditionToRemove);

            $condition = $this->buildWithoutTrashedCondition($column, $this->aliasName());
            return $this->whereIfNotSet($condition);
        }

        return $this;
    }
}
