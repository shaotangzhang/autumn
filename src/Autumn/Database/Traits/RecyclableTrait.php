<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Traits;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;
use Autumn\Lang\Date;

trait RecyclableTrait
{
    #[Index(Index::DEFAULT_INDEX_NAME)]
    #[Column(type: Column::TYPE_TIMESTAMP, name: self::COLUMN_DELETED_AT, priority: Column::PRIORITY_SOFT_DELETION)]
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * Returns the column name of the soft deletion
     * @return string
     */
    public static function column_deleted_at(): string
    {
        return self::COLUMN_DELETED_AT;
    }

    public function isTrashed(): bool
    {
        return $this->deletedAt && ($this->deletedAt->getTimestamp() < time());
    }

    public function getDeletedAt(): int
    {
        return $this->deletedAt?->getTimestamp() ?? 0;
    }

    /**
     * @param int|float|string|\DateTimeInterface|null $time
     */
    public function setDeletedAt(int|float|string|\DateTimeInterface $time = null): void
    {
        $this->deletedAt = isset($time) ? Date::of($time) : null;
    }

    public function getDeleteTime(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTimeInterface|null $deletedAt
     */
    public function setDeleteTime(?\DateTimeInterface $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }
}