<?php

namespace Autumn\Database\Traits;

use Autumn\Attributes\Transient;
use Autumn\Database\Db;
use Autumn\Database\Interfaces\EntityInterface;
use Autumn\Database\Interfaces\RelationInterface;
use Autumn\Database\Interfaces\RelationshipManyToManyInterface;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Models\Relation;
use Autumn\Database\Models\Repository;
use Autumn\Exceptions\SystemException;
use Autumn\Exceptions\ValidationException;

/**
 * Trait EntityRelationshipTrait
 *
 * Provides methods to define relationships between entities in the repository.
 */
trait EntityRelationshipTrait
{
    #[Transient]
    private array $relationshipCache = [];

    /**
     * Get the primary column(s) of the entity.
     *
     * @return string|array The primary column(s) of the entity.
     */
    protected function __primary_column__(): string|array
    {
        return Db::entity_primary_key(static::class);
    }

    /**
     * Generate a cache key for a relationship.
     *
     * @param string ...$args Arguments to generate the cache key.
     * @return string The generated cache key.
     */
    protected function __relation_cache_key__(string ...$args): string
    {
        return md5(serialize($args));
    }

    /**
     * Define a one-to-one relationship.
     *
     * @param string|EntityInterface $relatedEntity The related entity class or instance.
     * @param string|null $foreignKey The foreign key column. If null, it will be derived.
     * @param string|null $localKey The local key column. If null, it will be derived.
     * @return mixed The related entity instance or null if not found.
     * @throws ValidationException If the related entity is invalid or the local key value is invalid.
     */
    protected function hasOne(string|EntityInterface $relatedEntity, string $foreignKey = null, string $localKey = null): mixed
    {
        $relatedEntityClass = is_string($relatedEntity) ? $relatedEntity : $relatedEntity::class;
        if (!is_subclass_of($relatedEntityClass, EntityInterface::class)) {
            throw ValidationException::of('Invalid related entity: `%s`', $relatedEntityClass);
        }

        $foreignKey = $foreignKey ?: Db::entity_foreign_key($relatedEntityClass);
        $localKey = $localKey ?: $this->__primary_column__();

        $localKeyValue = $this->{$localKey};
        if ($localKeyValue === null || $localKeyValue === 0) {
            throw ValidationException::of(
                'The entity `%s` does not have a valid primary key value for `%s` relationship.',
                static::class, $relatedEntityClass
            );
        }

        $cacheKey = $this->__relation_cache_key__('hasOne', $relatedEntityClass, $foreignKey, $localKey, $localKeyValue);
        if (array_key_exists($cacheKey, $this->relationshipCache)) {
            return $this->relationshipCache[$cacheKey];
        }

        $relatedRepository = Repository::of($relatedEntityClass);

        $relatedObject = $relatedRepository
            ->and($foreignKey, $localKeyValue)
            ->query()
            ->fetch();

        $this->relationshipCache[$cacheKey] = $relatedObject;

        return $relatedObject;
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @param string|EntityInterface $targetEntity The target entity class or instance.
     * @param string|null $foreignKey The foreign key column. If null, it will be derived.
     * @param string|null $localKey The local key column. If null, it will be derived.
     * @return mixed The related entity instance or null if not found.
     * @throws ValidationException If the target entity is invalid or the foreign key value is invalid.
     */
    protected function belongsTo(string|EntityInterface $targetEntity, string $foreignKey = null, string $localKey = null): mixed
    {
        $targetEntityClass = is_string($targetEntity) ? $targetEntity : $targetEntity::class;
        if (!is_subclass_of($targetEntityClass, EntityInterface::class)) {
            throw ValidationException::of('Invalid target entity: `%s`', $targetEntityClass);
        }

        $foreignKey = $foreignKey ?: Db::entity_foreign_key($targetEntityClass);

        $foreignKeyValue = $this->{$foreignKey};
        if ($foreignKeyValue === null || $foreignKeyValue === 0) {
            throw ValidationException::of(
                'The entity `%s` does not have a valid primary key value for `%s` relationship.',
                static::class, $targetEntityClass
            );
        }

        $cacheKey = $this->__relation_cache_key__('belongsTo', $targetEntityClass, $foreignKey, $localKey, $foreignKeyValue);
        if (array_key_exists($cacheKey, $this->relationshipCache)) {
            return $this->relationshipCache[$cacheKey];
        }

        $targetRepository = Repository::of($targetEntityClass);

        $relatedObject = $targetRepository
            ->and(Db::entity_primary_key($targetEntityClass), $foreignKeyValue)
            ->query()
            ->fetch();

        $this->relationshipCache[$cacheKey] = $relatedObject;

        return $relatedObject;
    }

    /**
     * Define a one-to-many relationship.
     *
     * @param string $relatedEntityClass The related entity class.
     * @param string|null $foreignKey The foreign key column. If null, it will be derived.
     * @param string|null $localKey The local key column. If null, it will be derived.
     * @return RepositoryInterface The repository instance with the condition applied.
     * @throws ValidationException If the related entity is invalid or the local key value is invalid.
     */
    protected function hasMany(string $relatedEntityClass, string $foreignKey = null, string $localKey = null): RepositoryInterface
    {
        if (!is_subclass_of($relatedEntityClass, EntityInterface::class)) {
            throw ValidationException::of('Invalid related entity: `%s`', $relatedEntityClass);
        }

        $foreignKey = $foreignKey ?: Db::entity_foreign_key($relatedEntityClass);
        $localKey = $localKey ?: $this->__primary_column__();

        $localKeyValue = $this->{$localKey};
        if ($localKeyValue === null || $localKeyValue === 0) {
            throw ValidationException::of('The entity is not persisted yet.');
        }

        return Repository::of($relatedEntityClass)->and($foreignKey, $localKeyValue);
    }

    /**
     * Define a many-to-many relationship.
     *
     * @param string $relatedEntityClass The related entity class.
     * @param string $relationClass The pivot table name.
     * @param string|null $foreignPivotKey The foreign key column on the pivot table. If null, it will be derived.
     * @param string|null $relatedPivotKey The related key column on the pivot table. If null, it will be derived.
     * @param string|null $localKey The local key column. If null, it will be derived.
     * @return RelationshipManyToManyInterface The repository instance with the condition applied.
     * @throws ValidationException If the related entity is invalid or the local key value is invalid.
     */
    protected function belongsToMany(string $relatedEntityClass, string $relationClass, string $foreignPivotKey = null, string $relatedPivotKey = null, string $localKey = null): RepositoryInterface
    {
        if (!is_subclass_of($relatedEntityClass, EntityInterface::class)) {
            throw ValidationException::of('Invalid related entity: `%s`', $relatedEntityClass);
        }

        $cacheKey = $this->__relation_cache_key__('belongsToMany', $relatedEntityClass, $relationClass, $foreignPivotKey, $relatedPivotKey, $localKey);
        if (array_key_exists($cacheKey, $this->relationshipCache)) {
            $func = $this->relationshipCache[$cacheKey];
            return $func($this);
        }

        $foreignPivotKey = $foreignPivotKey ?: Db::entity_foreign_key(static::class);
        $relatedPivotKey = $relatedPivotKey ?: Db::entity_foreign_key($relatedEntityClass);
        $localKey = $localKey ?: $this->__primary_column__();

        $func = function ($self) use ($localKey, $relatedPivotKey, $foreignPivotKey, $relationClass, $relatedEntityClass) {
            $localKeyValue = $self->{$localKey};
            if ($localKeyValue === null || $localKeyValue === 0) {
                throw ValidationException::of('The entity is not persisted yet.');
            }

            return Repository::of($relatedEntityClass)
                ->alias('PRI')
                ->innerJoin($relationClass . ' AS R', 'R.' . $foreignPivotKey, 'PRI.' . $relatedPivotKey)
                ->and("R.$foreignPivotKey", $localKeyValue);
        };

        $this->relationshipCache[$cacheKey] = $func;
        return $func($this);
    }

    protected function manyToMany(string $relationClass, string $relatedEntityClass = null, string $foreignPivotKey = null, string $relatedPivotKey = null, string $localKey = null): RepositoryInterface
    {
        if (!is_subclass_of($relationClass, RelationInterface::class)) {
            throw ValidationException::of('Invalid relation class: `%s`', $relationClass);
        }

        $cacheKey = $this->__relation_cache_key__('manyToMany', $relationClass, $relatedEntityClass, $foreignPivotKey, $relatedPivotKey, $localKey);
        if (array_key_exists($cacheKey, $this->relationshipCache)) {
            $func = $this->relationshipCache[$cacheKey];
            return $func($this);
        }

        $relatedPivotKey = $relatedPivotKey ?: $relationClass::relation_secondary_column();
        $foreignPivotKey = $foreignPivotKey ?: $relationClass::relation_primary_column();

        if (!$relatedEntityClass) {
            if (!$relatedEntityClass = $relationClass::relation_secondary_class()) {
                throw SystemException::of('The relation `%s` has an incomplete secondary class.', $relationClass);
            }

            if (is_a(static::class, $relatedEntityClass, true)) {
                if (!$primaryClass = $relationClass::relation_primary_class()) {
                    throw SystemException::of('The relation `%s` has an incomplete primary class.', $relationClass);
                }

                if (!is_a(static::class, $primaryClass, true)) {
                    $relatedEntityClass = $primaryClass;
                    $relatedPivotKey = $relatedPivotKey ?: $relationClass::relation_primary_column();
                    $foreignPivotKey = $foreignPivotKey ?: $relationClass::relation_secondary_column();
                }
            }
        }

        $func = fn($self) => $self->belongsToMany($relatedEntityClass, $relationClass, $foreignPivotKey, $relatedPivotKey, $localKey);
        $this->relationshipCache[$cacheKey] = $func;
        return $func($this);
    }
}
