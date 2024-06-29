<?php

namespace Autumn\Database\Models;

use Autumn\Database\Interfaces\Creatable;
use Autumn\Database\Interfaces\Updatable;
use Autumn\Database\Traits\CreatableTrait;
use Autumn\Database\Traits\UpdatableTrait;

class TimestampedEntity extends Entity implements Creatable, Updatable
{
    use CreatableTrait;
    use UpdatableTrait;

    public const COLUMN_CREATED_AT = 'created_at';

    public const COLUMN_UPDATED_AT = 'updated_at';
}