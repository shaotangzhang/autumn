<?php
namespace Autumn\Interfaces;

use Autumn\Database\Interfaces\Persistable;

interface PersistingInterface
{
    /**
     * Persist the given Persistable object.
     *
     * @param Persistable $persistable The object to persist.
     * @return bool True on success, false on failure.
     */
    public function persist(Persistable $persistable): bool;

    /**
     * Destroy the object with the given ID.
     *
     * @param int $id The ID of the object to destroy.
     * @return bool True if the object was destroyed successfully, false otherwise.
     */
    public function destroy(int $id): bool;

    /**
     * Check if an object exists based on the provided context.
     *
     * @param array $context The context used for existence check.
     * @return bool True if the object exists, false otherwise.
     */
    public function exists(array $context): bool;

    /**
     * Query objects based on the provided context.
     *
     * @param array $context The context used for querying.
     * @return iterable An iterable collection of objects matching the query.
     */
    public function query(array $context): iterable;
}