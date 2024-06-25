<?php
/**
 * Autumn PHP Framework
 *
 * Date:        24/06/2024
 */

namespace Autumn\Database\Traits;

use Autumn\Database\Db;

trait RecyclableEntityRepositoryTrait
{
    use EntityRepositoryTrait;

    private function buildOnlyTrashedCondition(string $column, string $alias = null): string
    {
        return "($alias$column IS NOT NULL AND $alias$column <= CURRENT_TIMESTAMP())";
    }

    private function buildWithoutTrashedCondition(string $column, string $alias = null): string
    {
        return "($alias$column IS NULL OR $alias$column > CURRENT_TIMESTAMP())";
    }

    public function onlyTrashed(): static
    {
        if ($column = Db::entity_deleted_at(static::class)) {
            $conditionToRemove = $this->buildWithoutTrashedCondition($column, $this->aliasName());
            $this->removeWhere($conditionToRemove);

            $condition = $this->buildOnlyTrashedCondition($column, $this->aliasName());
            return $this->whereIfNotSet($condition);
        }

        return $this;
    }

    public function withoutTrashed(): static
    {
        if ($column = Db::entity_deleted_at(static::class)) {
            $conditionToRemove = $this->buildOnlyTrashedCondition($column, $this->aliasName());
            $this->removeWhere($conditionToRemove);

            $condition = $this->buildWithoutTrashedCondition($column, $this->aliasName());
            return $this->whereIfNotSet($condition);
        }

        return $this;
    }
}