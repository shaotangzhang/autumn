<?php

namespace Autumn\Database\Models;

use Autumn\Database\Interfaces\Extendable;

class ExtendedEntity extends AbstractEntity implements Extendable
{
    public const COLUMN_PRIMARY_KEY = 'id';
    public const IGNORE_DUPLICATE_ON_CREATE = false;
    public const RELATION_PRIMARY_CLASS = null;

    public const RELATION_PRIMARY_COLUMN = null;

    public const RELATION_SECONDARY_COLUMN = null;


    public static function relation_primary_class(): string
    {
        return static::RELATION_PRIMARY_CLASS;
    }

    public static function relation_primary_column(): string
    {
        return static::RELATION_PRIMARY_COLUMN ?: static::COLUMN_PRIMARY_KEY;
    }

    public static function relation_secondary_column(): ?string
    {
        return static::RELATION_SECONDARY_COLUMN;
    }

    public static function column_primary_key(): string|array
    {
        $primaryKey = static::relation_primary_column();
        if ($secondaryKey = static::relation_secondary_column()) {
            return [$primaryKey, $secondaryKey];
        }
        return $primaryKey;
    }

    public function getId(): int
    {
        return $this->__get(static::relation_primary_column());
    }

    public function isNew(): bool
    {
        return false;
    }

    public function pk(): int|array
    {
        $primaryKey = static::relation_primary_column();

        if ($secondaryKey = static::relation_secondary_column()) {
            return [
                $primaryKey => $this->__get($primaryKey),
                $secondaryKey => $this->__get($secondaryKey)
            ];
        }

        return $this->__get($primaryKey);
    }
}