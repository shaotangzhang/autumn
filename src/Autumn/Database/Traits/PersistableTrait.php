<?php
/**
 * Autumn PHP Framework
 *
 * Date:        8/05/2024
 */

namespace Autumn\Database\Traits;

use Autumn\Database\Attributes\Column;

trait PersistableTrait
{
    #[Column(type: Column::ID, name: self::COLUMN_PRIMARY_KEY, auto: true, priority: Column::PRIORITY_PK)]
    private int $id = 0;

    public static function column_primary_key(): string
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
        return $this->id <= 0;
    }
}