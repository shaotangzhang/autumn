<?php
/**
 * Autumn PHP Framework
 *
 * Date:        20/06/2024
 */

namespace Autumn\Database\Models;

use Autumn\Database\Attributes\Index;
use Autumn\Database\Interfaces\RelationInterface;

#[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE, self::RELATION_PRIMARY_COLUMN, self::RELATION_SECONDARY_COLUMN)]
class Relation extends ExtendedEntity implements RelationInterface
{
    public const RELATION_SECONDARY_COLUMN = 'secondary_id';
    public const RELATION_SECONDARY_CLASS = null;
    public const IGNORE_DUPLICATE_ON_CREATE = false;

    public static function relation_primary_column(): string
    {
        return static::RELATION_PRIMARY_COLUMN;
    }

    public static function relation_primary_class(): string
    {
        return static::RELATION_PRIMARY_CLASS;
    }

    public static function relation_secondary_column(): string
    {
        return static::RELATION_SECONDARY_COLUMN;
    }

    public static function relation_secondary_class(): string
    {
        return static::RELATION_SECONDARY_CLASS;
    }


    public function getSecondaryId(): int
    {
        if ($column = static::entity_column(static::relation_secondary_column())) {
            return $column->getProperty()->getValue($this);
        }

        return 0;
    }

    public function secondary(): mixed
    {
        return $this->hasOne(static::relation_secondary_class(), static::relation_secondary_column());
    }
}