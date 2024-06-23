<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/06/2024
 */

namespace Autumn\Database\Models;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Interfaces\Recyclable;

class RecyclableEntity extends Entity implements Recyclable
{
    public const COLUMN_DELETED_AT = 'deleted_at';

    #[Column(type: Column::TYPE_TIMESTAMP, name: 'deleted_at', priority: Column::PRIORITY_SOFT_DELETION)]
    private ?\DateTimeInterface $deletedAt = null;

    public static function column_deleted_at(): string
    {
        return static::COLUMN_DELETED_AT;
    }

    public function isTrashed(): bool
    {
        return ($time = $this->deletedAt?->getTimestamp()) && ($time < time());
    }

    public function getDeletedAt(): int
    {
        return $this->deletedAt?->getTimestamp() ?? 0;
    }

    public function getDeleteTime(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }
}