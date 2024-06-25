<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Models;

use Autumn\Database\Interfaces\Recyclable;
use Autumn\Database\Traits\RecyclableTrait;

class RecyclableEntity extends Entity implements Recyclable
{
    use RecyclableTrait;

    public const COLUMN_DELETED_AT = 'deleted_at';

    public static function column_deleted_at(): string
    {
        return static::COLUMN_DELETED_AT;
    }
}