<?php

namespace Autumn\Database\Models;

use Autumn\Database\Interfaces\RelationInterface;

/**
 * Abstract class representing a many-to-many relation entity.
 * This class is meant to be extended by concrete relation classes.
 */
abstract class Relation extends ExtendedEntity implements RelationInterface
{
    public const ONE_TO_ONE = 'ONE_TO_ONE';
    public const ONE_TO_MANY = 'ONE_TO_MANY';
    public const MANY_TO_MANY = 'MANY_TO_MANY';

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
    public const IGNORE_DUPLICATE_ON_CREATE = true;

    public static function relation_secondary_class(): ?string
    {
        return static::RELATION_SECONDARY_CLASS;
    }

    public static function relationship(): string
    {
        if (static::relation_secondary_column()) {
            if (static::relation_secondary_class()) {
                return static::MANY_TO_MANY;
            }
            return static::ONE_TO_MANY;
        }
        return static::ONE_TO_ONE;
    }

}
