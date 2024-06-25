<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Models;

use Autumn\Database\DbConnection;
use Autumn\Database\Interfaces\Extendable;
use Autumn\Database\Traits\EntityPersistTrait;
use Autumn\Database\Traits\ExtendedEntityTrait;

class ExtendedEntity extends AbstractEntity implements Extendable
{
    use ExtendedEntityTrait;
    use EntityPersistTrait;

    public const RELATION_PRIMARY_CLASS = null;
    public const RELATION_PRIMARY_COLUMN = 'primary_id';
    public const IGNORE_DUPLICATE_ON_CREATE = false;

    public static function column_created_at(): string
    {
        return '';
    }

    public static function column_updated_at(): string
    {
        return '';
    }
}