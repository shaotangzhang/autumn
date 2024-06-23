<?php

namespace Autumn\Database\Traits;

use Autumn\Database\Interfaces\Recyclable;

trait RecyclableRepositoryTrait
{
    private function buildOnlyTrashedCondition(string $column, string $alias = null): string
    {
        return "($alias$column IS NOT NULL AND $alias$column <= CURRENT_TIMESTAMP())";
    }

    private function buildWithoutTrashedCondition(string $column, string $alias = null): string
    {
        return "($alias$column IS NULL OR $alias$column > CURRENT_TIMESTAMP())";
    }

    public static function entity_recyclable_column(string $alias = null): ?string
    {
        if ($class = static::entity_class()) {
            if (is_subclass_of($class, Recyclable::class)) {
                if ($column = $class::column_deleted_at()) {
                    if ($alias && !str_ends_with($alias, '.')) {
                        $alias .= '.';
                    }
                    return $alias . $column;
                }
            }
        }

        return null;
    }

    public function onlyTrashed(): static
    {
        if ($column = static::entity_recyclable_column($this->aliasName())) {
            $this->removeWhere($this->buildWithoutTrashedCondition($column));
            return $this->whereIfNotSet($this->buildOnlyTrashedCondition($column));
        }
        return $this;
    }

    public function withoutTrashed(): static
    {
        if ($column = static::entity_recyclable_column($this->aliasName())) {
            $this->removeWhere($this->buildOnlyTrashedCondition($column));
            return $this->whereIfNotSet($this->buildWithoutTrashedCondition($column));
        }
        return $this;
    }
}
