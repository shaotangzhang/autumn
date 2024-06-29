<?php

namespace Autumn\Database\Traits;

use Autumn\Database\Db;
use Autumn\Database\DbException;
use Autumn\Database\Interfaces\RepositoryInterface;

/**
 * Trait RelationManagerTrait
 *
 * @deprecated Not complete yet.
 *
 * @package     Autumn\Database\Traits
 * @since       29/06/2024
 */
trait RelationManagerTrait
{
    use RelationRepositoryTrait;

    public static function find(array|int $context): ?static
    {
        return static::findBy($context)->query()->fetch();
    }

    public static function findBy(array|int $context): RepositoryInterface
    {
        if (is_int($context)) {
            $context = [static::relation_primary_column() => $context];
        }

        return static::of($context);
    }

    public static function findOrNew(array|int $context, array $extra = null): static
    {
        if ($instance = static::find($context)) {
            return $instance;
        }

        $data = array_merge($extra ?? [], is_array($context) ? $context : []);
        return static::from($data);
    }

    public static function attach(self $relation): bool
    {
        try {
            Db::forEntity($relation::class)->insert(static::entity_name(), $relation->toArray(), $relation::IGNORE_DUPLICATE_ON_CREATE);
            return true;
        } catch (DbException) {
            return false;
        }
    }

    public static function detach(self $relation): bool
    {
        try {
            return Db::forEntity($relation::class)->delete(static::entity_name(), $relation->toArray()) > 0;
        } catch (DbException) {
            return false;
        }
    }

//    public static function sync(self ...$relations): int
//    {
//
//    }
}