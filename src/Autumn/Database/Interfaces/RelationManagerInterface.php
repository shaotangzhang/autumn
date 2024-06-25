<?php
/**
 * Autumn PHP Framework
 *
 * Date:        8/05/2024
 */

namespace Autumn\Database\Interfaces;


interface RelationManagerInterface
{
    /**
     * Attach a related entity to the current entity.
     *
     * @param int|string $relatedId Identifier of the related entity
     * @return bool True on success, false on failure
     */
    public function attach(int|string $relatedId): bool;

    /**
     * Detach a related entity from the current entity.
     *
     * @param int|string $relatedId Identifier of the related entity
     * @return bool True on success, false on failure
     */
    public function detach(int|string $relatedId): bool;

    /**
     * Sync related entities with the current entity.
     *
     * @param array $relatedIds Identifiers of related entities to sync
     * @return bool True on success, false on failure
     */
    public function sync(array $relatedIds): bool;

    /**
     * Check if a related entity is attached to the current entity.
     *
     * @param int|string $relatedId Identifier of the related entity
     * @return bool True if related entity is attached, false otherwise
     */
    public function isAttached(int|string $relatedId): bool;
}
