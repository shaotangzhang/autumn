<?php
/**
 * Autumn PHP Framework
 *
 * Date:        20/06/2024
 */

namespace Autumn\Database\Models;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Interfaces\Persistable;
use Autumn\Database\Traits\CreatableTrait;
use Autumn\Database\Traits\UpdatableTrait;

class Entity extends AbstractEntity implements Persistable
{
    use CreatableTrait;
    use UpdatableTrait;

    public const ENTITY_PRIMARY_COLUMN = 'id';
    public const COLUMN_CREATED_AT = 'created_at';
    public const COLUMN_UPDATED_AT = 'updated_at';
    public const IGNORE_DUPLICATE_ON_CREATE = false;

    #[Column(type: Column::ID, name: 'id', unsigned: true, priority: Column::PRIORITY_PK)]
    private int $id = 0;

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
        return $this->id < 1;
    }
}