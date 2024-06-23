<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Models;

use Autumn\Database\Interfaces\Extendable;

class ExtendedEntity extends AbstractEntity implements Extendable
{
    public const RELATION_PRIMARY_COLUMN = 'primary_id';
    public const RELATION_PRIMARY_CLASS = null;

    public static function relation_primary_column(): string
    {
        return static::RELATION_PRIMARY_COLUMN;
    }

    public static function relation_primary_class(): string
    {
        return static::RELATION_PRIMARY_CLASS;
    }

    public function getPrimaryId(): int
    {
        if ($column = static::entity_column(static::relation_primary_column())) {
            return $column->getProperty()->getValue($this);
        }

        return 0;
    }

    public function primary(): mixed
    {
        return $this->hasOne(static::relation_primary_class(), static::relation_primary_column());
    }
}