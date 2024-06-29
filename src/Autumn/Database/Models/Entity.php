<?php

namespace Autumn\Database\Models;

use Autumn\Database\Interfaces\Persistable;
use Autumn\Database\Traits\PersistableTrait;

class Entity extends AbstractEntity implements Persistable
{
    use PersistableTrait;
    public const COLUMN_PRIMARY_KEY = 'id';
    public const IGNORE_DUPLICATE_ON_CREATE = false;
}