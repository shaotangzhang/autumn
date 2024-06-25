<?php
/**
 * Autumn PHP Framework
 *
 * Date:        24/06/2024
 */

namespace Autumn\Database\Traits;

use Autumn\Attributes\Transient;
use Autumn\Database\Db;
use Autumn\Database\DbConnection;
use Autumn\Database\Interfaces\EntityInterface;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Models\Repository;
use Autumn\Exceptions\ValidationException;

trait RelatedRepositoryTrait
{
    // use RepositoryTrait;

    #[Transient]
    private array $relationshipCache = [];

    public static function of(string|EntityInterface $related, array $context = null, DbConnection $connection = null): static
    {
        return new static($related, $context, $connection);
    }

    protected function getModelPrimaryKey(): string
    {
        return Db::entity_primary_key(static::class);
    }

    protected function getRelationshipCacheKey(string ...$args): string
    {
        return md5(serialize($args));
    }

    public function hasOne(string|EntityInterface $relatedEntity, string $foreignKey = null, string $localKey = null): ?EntityInterface
    {
        $relatedEntityClass = is_string($relatedEntity) ? $relatedEntity : $relatedEntity::class;
        if (!is_subclass_of($relatedEntityClass, EntityInterface::class)) {
            throw ValidationException::of('Invalid related entity: `%s`', $relatedEntityClass);
        }

        $foreignKey = $foreignKey ?: Db::entity_foreign_key($relatedEntityClass);
        $localKey = $localKey ?: $this->getModelPrimaryKey();

        $localKeyValue = $this->{$localKey};
        if ($localKeyValue === null || $localKeyValue === 0) {
            throw ValidationException::of(
                'The entity `%s` does not have a valid primary key value for `%s` relationship.',
                static::class, $relatedEntityClass
            );
        }

        $cacheKey = $this->getRelationshipCacheKey('hasOne', $relatedEntityClass, $foreignKey, $localKey, $localKeyValue);
        if (array_key_exists($cacheKey, $this->relationshipCache)) {
            return $this->relationshipCache[$cacheKey];
        }

        $relatedRepository = Repository::of($relatedEntityClass);

        $relatedObject = $relatedRepository
            ->where($foreignKey, '=', $localKeyValue)
            ->query()
            ->fetch();

        $this->relationshipCache[$cacheKey] = $relatedObject;

        return $relatedObject;
    }

    public function belongsTo(string|EntityInterface $targetEntity, string $foreignKey = null, string $localKey = null): ?EntityInterface
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

        $cacheKey = $this->getRelationshipCacheKey('belongsTo', $targetEntityClass, $foreignKey, $localKey, $foreignKeyValue);
        if (array_key_exists($cacheKey, $this->relationshipCache)) {
            return $this->relationshipCache[$cacheKey];
        }

        $targetRepository = Repository::of($targetEntityClass);

        $relatedObject = $targetRepository
            ->where($targetRepository->getModelPrimaryKey(), '=', $foreignKeyValue)
            ->query()
            ->fetch();

        $this->relationshipCache[$cacheKey] = $relatedObject;

        return $relatedObject;
    }

    public function hasMany(string $relatedEntityClass, string $foreignKey = null, string $localKey = null): RepositoryInterface
    {
        if (!is_subclass_of($relatedEntityClass, EntityInterface::class)) {
            throw ValidationException::of('Invalid related entity: `%s`', $relatedEntityClass);
        }

        $foreignKey = $foreignKey ?: Db::entity_foreign_key($relatedEntityClass);
        $localKey = $localKey ?: $this->getModelPrimaryKey();

        $localKeyValue = $this->{$localKey};
        if ($localKeyValue === null || $localKeyValue === 0) {
            throw ValidationException::of('The entity is not persisted yet.');
        }

        return Repository::of($relatedEntityClass)
            ->where($foreignKey, '=', $localKeyValue);
    }

    public function belongsToMany(string $relatedEntityClass, string $pivotTable, string $foreignPivotKey = null, string $relatedPivotKey = null, string $localKey = null, string $relatedKey = null): RepositoryInterface
    {
        if (!is_subclass_of($relatedEntityClass, EntityInterface::class)) {
            throw ValidationException::of('Invalid related entity: `%s`', $relatedEntityClass);
        }

        $foreignPivotKey = $foreignPivotKey ?: Db::entity_foreign_key(static::class);
        $relatedPivotKey = $relatedPivotKey ?: Db::entity_foreign_key($relatedEntityClass);
        $localKey = $localKey ?: $this->getModelPrimaryKey();

        $localKeyValue = $this->{$localKey};
        if ($localKeyValue === null || $localKeyValue === 0) {
            throw ValidationException::of('The entity is not persisted yet.');
        }

        return Repository::of($relatedEntityClass)
            ->innerJoin($pivotTable, $foreignPivotKey, $relatedPivotKey)
            ->where("$pivotTable.$foreignPivotKey", '=', $localKeyValue);
    }
}