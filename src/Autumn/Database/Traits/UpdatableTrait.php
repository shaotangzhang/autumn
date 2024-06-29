<?php

namespace Autumn\Database\Traits;

use Autumn\Database\Attributes\Column;
use Autumn\Lang\Date;

trait UpdatableTrait
{
    #[Column(type: Column::TYPE_TIMESTAMP, name: self::COLUMN_UPDATED_AT, currentTimestampOnCreate: true, currentTimestampOnUpdate: true, priority: Column::PRIORITY_TIMESTAMPS)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * Returns the column name of update time
     * @return string
     */
    public static function column_updated_at(): string
    {
        return self::COLUMN_UPDATED_AT;
    }

    public function getUpdatedAt(): int
    {
        return $this->updatedAt?->getTimestamp() ?? 0;
    }

    /**
     * @param int|float|string|\DateTimeInterface|null $time
     */
    public function setUpdatedAt(int|float|string|\DateTimeInterface $time = null): void
    {
        $this->updatedAt = isset($time) ? Date::of($time) : null;
    }

    public function getUpdateTime(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeInterface|null $updatedAt
     */
    public function setUpdateTime(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}