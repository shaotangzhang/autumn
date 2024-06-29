<?php

namespace App\Models\Developer;

use Autumn\Database\Attributes\Index;
use Autumn\Database\Models\ExtendedEntity;
use Autumn\Database\Traits\RelationManagerTrait;
use Autumn\Extensions\Auth\Models\Traits\IPv4ColumnTrait;
use Autumn\Extensions\Auth\Models\Traits\UserIdColumnTrait;

#[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE, self::RELATION_PRIMARY_COLUMN, self::RELATION_SECONDARY_COLUMN)]
class DeveloperIp extends ExtendedEntity
{
    use UserIdColumnTrait;
    use IPv4ColumnTrait;
    use RelationManagerTrait;

    public const ENTITY_NAME = 'auth_developer_ips';

    public const COLUMN_PRIMARY_KEY = null;

    public const RELATION_PRIMARY_COLUMN = 'user_id';
    public const RELATION_SECONDARY_COLUMN = 'ip';

    public const RELATION_PRIMARY_CLASS = Developer::class;


    public static function column_primary_key(): string|array
    {
        $primaryKey = static::relation_primary_column();
        if ($secondaryKey = static::relation_secondary_column()) {
            return [$primaryKey, $secondaryKey];
        }
        return $primaryKey;
    }

    public function user(): ?Developer
    {
        return $this->hasOne(Developer::class, static::RELATION_PRIMARY_COLUMN);
    }
}