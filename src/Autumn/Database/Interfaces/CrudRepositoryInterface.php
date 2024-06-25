<?php
/**
 * Autumn PHP Framework
 *
 * Date:        21/05/2024
 */

namespace Autumn\Database\Interfaces;

interface CrudRepositoryInterface
{
    public function getDefaultCriteria(): array;

    public function setDefaultCriteria(array $criteria = null): void;

    public function getDefaultSorting(): array;

    public function setDefaultSorting(array $sorting = null): void;

    public function create(array|Persistable $entity): false|Persistable;

    public function update(int|Updatable $entity, array $changes = null): bool;

    public function delete(int|Persistable $entity): bool;

    public function trash(int|Recyclable $entity): bool;

    public function restore(int|Recyclable $entity): bool;

    public function fetchList(array $context = null, string|callable $callback = null, int $mode = null): mixed;

    public function fetchBy(string $column, mixed $value, array $context = null, string|callable $callback = null, int $mode = null): mixed;

    public function fetchById(int $id, array $context = null, string|callable $callback = null, int $mode = null): mixed;

    public function getNone(): RepositoryInterface;

    public function getList(array $context = null): RepositoryInterface;

    public function queryBy(string $column, mixed $value, array $context = null): RepositoryInterface;

    public function queryById(int $id, array $context = null): RepositoryInterface;

    public function queryByRequest(array|\ArrayAccess $request, array $context = null, array &$args = null): RepositoryInterface;

    public function queryByScope(string $scope, array $context = null): RepositoryInterface;
}