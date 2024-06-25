<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Models;

use Autumn\Database\Interfaces\RelationInterface;
use Autumn\Database\Traits\RelationTrait;

/**
 * Abstract class representing a many-to-many relation entity.
 * This class is meant to be extended by concrete relation classes.
 */
abstract class Relation extends AbstractEntity implements RelationInterface
{
    use RelationTrait;

    /**
     * The class name of the primary entity in the relation.
     */
    public const RELATION_PRIMARY_CLASS = null;

    /**
     * The class name of the secondary entity in the relation.
     */
    public const RELATION_SECONDARY_CLASS = null;

    /**
     * The column name for the primary entity ID in the relation table.
     */
    public const RELATION_PRIMARY_COLUMN = null;

    /**
     * The column name for the secondary entity ID in the relation table.
     */
    public const RELATION_SECONDARY_COLUMN = null;

    /**
     * Whether to ignore duplicate entries on creation.
     */
    public const IGNORE_DUPLICATE_ON_CREATE = false;

    /**
     * Returns the primary key columns for the relation table.
     *
     * @return string|array
     */
    public static function column_primary_key(): string|array
    {
        return [static::RELATION_PRIMARY_COLUMN, static::RELATION_SECONDARY_COLUMN];
    }

    /**
     * Gets the ID of the relation.
     * Since relations don't have a single ID, this always returns 0.
     *
     * @return int
     */
    public function getId(): int
    {
        return 0;
    }

    /**
     * Sets the ID of the relation.
     * This method does nothing because relations don't have a single ID.
     *
     * @param int $id
     */
    public function setId(int $id): void
    {
        // do nothing
    }

    /**
     * Checks if the relation is new (not yet saved in the database).
     *
     * @return bool
     */
    public function isNew(): bool
    {
        return !$this->getPrimaryId() && !$this->getSecondaryId();
    }

    /**
     * Returns the primary key values for the relation.
     *
     * @return int|array
     */
    public function pk(): int|array
    {
        return [
            static::RELATION_PRIMARY_COLUMN => $this->getPrimaryId(),
            static::RELATION_SECONDARY_COLUMN => $this->getSecondaryId()
        ];
    }
}
