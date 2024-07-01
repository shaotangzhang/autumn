<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/05/2024
 */

namespace Autumn\System\Services;

use Autumn\Database\Interfaces\Creatable;
use Autumn\Database\Interfaces\Persistable;
use Autumn\Database\Interfaces\Recyclable;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Interfaces\Updatable;

/**
 * Interface CrudServiceInterface
 *
 * Provides a common interface for CRUD operations.
 */
interface CrudServiceInterface extends ServiceInterface
{
    /**
     * Creates a new entity.
     *
     * @param array|Creatable $entity The entity to create, can be an array of attributes or an object implementing Creatable.
     * @return false|Creatable True on success, false on failure.
     */
    public function create(array|Persistable $entity): false|Persistable;

    /**
     * Updates an existing entity.
     *
     * @param int|Updatable $entity The entity to update, can be an ID or an object implementing Updatable.
     * @param array|null $changes The changes to apply, if entity is provided as an ID.
     * @return bool True on success, false on failure.
     */
    public function update(int|Updatable $entity, array $changes = null): bool;

    /**
     * Deletes an entity permanently.
     *
     * @param int|Persistable $entity The entity to delete, can be an ID or an object implementing Persistable.
     * @return bool True on success, false on failure.
     */
    public function delete(int|Persistable $entity): bool;

    /**
     * Moves an entity to the trash.
     *
     * @param int|Recyclable $entity The entity to trash, can be an ID or an object implementing Recyclable.
     * @return bool True on success, false on failure.
     */
    public function trash(int|Recyclable $entity): bool;

    /**
     * Restores an entity from the trash.
     *
     * @param int|Recyclable $entity The entity to restore, can be an ID or an object implementing Recyclable.
     * @return bool True on success, false on failure.
     */
    public function restore(int|Recyclable $entity): bool;

    /**
     * Retrieves a list of entities based on the provided context.
     *
     * @param array|null $context Optional context for filtering and pagination.
     * @return RepositoryInterface The repository containing the list of entities.
     */
    public function getList(array $context = null): RepositoryInterface;

    /**
     * Requests for a single entity by its ID.
     *
     * @param int $id The ID of the entity to query.
     * @param array|null $context Optional context for additional query parameters.
     * @return RepositoryInterface The repository containing the queried entity.
     */
    public function queryById(int $id, array $context = null): RepositoryInterface;
}
