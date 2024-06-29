<?php
namespace Autumn\Database\Traits;

use Autumn\Database\Attributes\Column;
use Autumn\Lang\Date;

trait ExpirableTrait
{
    #[Column(type: Column::TYPE_TIMESTAMP, name: 'expired_at', priority: Column::PRIORITY_TIMESTAMPS)]
    private ?\DateTimeInterface $expiredAt = null;

    /**
     * Returns the column name of create time.
     * @return string
     */
    public static function column_expired_at(): string
    {
        return 'expired_at';
    }

    public function isExpired(): bool
    {
        return $this->expiredAt && ($this->expiredAt?->getTimestamp() < time());
    }

    public function getExpiredAt(): int
    {
        return $this->expiredAt?->getTimestamp() ?? 0;
    }

    /**
     * @param int|float|string|\DateTimeInterface|null $time
     */
    public function setExpiredAt(int|float|string|\DateTimeInterface $time = null): void
    {
        $this->expiredAt = isset($time) ? Date::of($time) : null;
    }

    public function getExpiryTime(): ?\DateTimeInterface
    {
        return $this->expiredAt;
    }

    /**
     * @param \DateTimeInterface|null $expiredAt
     */
    public function setExpiryTime(?\DateTimeInterface $expiredAt): void
    {
        $this->expiredAt = $expiredAt;
    }
}