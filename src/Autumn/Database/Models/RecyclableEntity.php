<?php
namespace Autumn\Database\Models;

use Autumn\Database\Interfaces\Recyclable;
use Autumn\Database\Traits\RecyclableTrait;

class RecyclableEntity extends TimestampedEntity implements Recyclable
{
    use RecyclableTrait;

    public const COLUMN_DELETED_AT = 'deleted_at';
}