<?php
/**
 * Autumn PHP Framework
 *
 * Date:        9/05/2024
 */

namespace Autumn\Database\Interfaces;

/**
 * Interface RecyclableRepositoryInterface
 *
 * This interface extends the base RepositoryInterface to include methods for handling soft-deleted records.
 */
interface RecyclableRepositoryInterface extends RepositoryInterface
{
    /**
     * Retrieve only soft-deleted records from the repository.
     *
     * @return static
     */
    public function onlyTrashed(): static;

    /**
     * Retrieve records excluding soft-deleted records from the repository.
     *
     * @return static
     */
    public function withoutTrashed(): static;
}
