<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Traits;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\JsonIgnore;
use Autumn\Lang\Date;

trait PublishedAtColumnTrait
{
    #[JsonIgnore(ignore: JsonIgnore::IGNORE_NULL)]
    #[Column(type: Column::TYPE_TIMESTAMP, name: 'published_at', priority: Column::PRIORITY_TIMESTAMPS)]
    private ?\DateTimeInterface $publishedAt = null;

    public function isPublished(): bool
    {
        return $this->publishedAt && ($this->publishedAt <= time());
    }

    /**
     * @return int
     */
    public function getPublishedAt(): int
    {
        return $this->publishedAt?->getTimestamp() ?? 0;
    }

    /**
     * @param int|float|string|\DateTimeInterface|null $publishedAt
     */
    public function setPublishedAt(int|float|string|\DateTimeInterface|null $publishedAt): void
    {
        $this->publishedAt = Date::fromInput($publishedAt);
    }

    public function getPublishTime(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishTime(?\DateTimeInterface $time): void
    {
        $this->publishedAt = $time;
    }
}