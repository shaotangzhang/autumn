<?php
namespace Autumn\Database\Traits;

use Autumn\Database\Attributes\Column;
use Autumn\Lang\Date;

trait CreatableTrait
{
    #[Column(type: Column::TYPE_TIMESTAMP, name: self::COLUMN_CREATED_AT, currentTimestampOnCreate: true, priority: Column::PRIORITY_TIMESTAMPS)]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * Returns the column name of create time.
     * @return string
     */
    public static function column_created_at(): string
    {
        return self::COLUMN_CREATED_AT;
    }

    public function getCreatedAt(): int
    {
        return $this->createdAt?->getTimestamp() ?? 0;
    }

    /**
     * @param int|float|string|\DateTimeInterface|null $time
     */
    public function setCreatedAt(int|float|string|\DateTimeInterface $time = null): void
    {
        $this->createdAt = isset($time) ? Date::of($time) : null;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface|null $createdAt
     */
    public function setCreateTime(?\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}