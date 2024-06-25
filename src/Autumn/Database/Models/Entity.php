<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Models;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Interfaces\Creatable;
use Autumn\Database\Interfaces\Updatable;
use Autumn\Database\Traits\CreatableTrait;
use Autumn\Database\Traits\EntityManagerTrait;
use Autumn\Database\Traits\UpdatableTrait;

class Entity extends AbstractEntity implements Creatable, Updatable
{
    use EntityManagerTrait;
    use CreatableTrait, UpdatableTrait;

    public const COLUMN_PRIMARY_KEY = 'id';
    public const COLUMN_CREATED_AT = 'created_at';
    public const COLUMN_UPDATED_AT = 'updated_at';
    public const IGNORE_DUPLICATE_ON_CREATE = false;

    #[Column(type: Column::ID, name: 'id')]
    private int $id = 0;

    public static function column_primary_key(): string|array
    {
        return static::COLUMN_PRIMARY_KEY;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function isNew(): bool
    {
        return $this->id > 0;
    }

    public function pk(): int|array
    {
        return $this->id;
    }
}