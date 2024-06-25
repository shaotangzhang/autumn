<?php
/**
 * Autumn PHP Framework
 *
 * Date:        23/06/2024
 */

namespace Autumn\Database\Interfaces;

use Autumn\Database\DbConnection;

interface EntityManagerInterface extends EntityInterface
{
    public static function find(int|array $context): ?static;

    public static function findOrFail(int|array $context, string $messageIfNotFound=null): static;

    public static function findOrNew(int|array $context, array $extra = null): static;

    public static function findOrCreate(int|array $context, array $extra = null): static;

    public static function repository(array $context = null, DbConnection $connection = null): RepositoryInterface;

    public static function create(array|self $entity): static;

    public static function update(int|self $entity, array $changes = null): static;

    public static function delete(int|self $entity): static;

    public function save(array $changes = null): bool;

    public function destroy(): bool;
}