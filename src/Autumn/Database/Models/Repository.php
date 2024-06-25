<?php

namespace Autumn\Database\Models;

use Autumn\Database\DbConnection;
use Autumn\Database\Interfaces\EntityInterface;
use Autumn\Database\Interfaces\Recyclable;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Traits\RepositoryTrait;
use Autumn\Exceptions\ValidationException;

class Repository implements \IteratorAggregate, RepositoryInterface
{
    use RepositoryTrait;

    public static function of(EntityInterface|string $entity, array $context = null, DbConnection $connection = null)
    {
        $entityClass = is_string($entity) ? $entity : $entity::class;
        if (!is_subclass_of($entityClass, EntityInterface::class)) {
            throw ValidationException::of('Invalid entity type: `%s`.', $entityClass);
        }

        static $classes;
        if ($class = $entityClass[$entityClass] ?? null) {
            return new $class($context, $connection);
        }

        if(is_subclass_of($entityClass, Recyclable::class)) {

        }

        return new class extends Repository {

        };
    }
}
