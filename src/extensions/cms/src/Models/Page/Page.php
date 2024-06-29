<?php

namespace Autumn\Extensions\Cms\Models\Page;

use Autumn\Attributes\Transient;
use Autumn\Database\Attributes\Index;
use Autumn\Database\Interfaces\RecyclableRepositoryInterface;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Traits\RecyclableEntityManagerTrait;
use Autumn\Extensions\Cms\Interfaces\Categorized;
use Autumn\Extensions\Cms\Interfaces\MetaSupportedInterface;
use Autumn\Extensions\Cms\Models\Media\Media;
use Autumn\Extensions\Cms\Models\Tag\Tag;
use Autumn\Extensions\Cms\Models\Traits\CategoryIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\MetaSupportedTrait;
use Autumn\Extensions\Cms\Models\Traits\MultipleSitesRepositoryTrait;
use Autumn\Extensions\Cms\Models\Widgets\Widget;

#[Index('idx_category', Index::INDEX, 'category_id')]
class Page extends PageEntity implements Categorized, RecyclableRepositoryInterface, MetaSupportedInterface
{
    use RecyclableEntityManagerTrait;
    use MultipleSitesRepositoryTrait;
    use CategoryIdColumnTrait;
    use MetaSupportedTrait;

    public const RELATION_META = PageMeta::class;

    #[Transient]
    private array $items = [];

    /**
     * @return array
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * @param iterable $items
     */
    public function setItems(iterable $items): void
    {
        if (is_array($items)) {
            $this->items = $items;
        } else {
            $this->items = iterator_to_array($items);
        }
    }

    public function hasItems(): int
    {
        return count($this->items);
    }

    public static function defaultUrlPath(): ?string
    {
        return env('CMS_URL_PAGES_PATH', '/pages/index');
    }

    public static function defaultUrlPrefix(): ?string
    {
        return env('CMS_URL_PAGES_PREFIX', '/pages/');
    }

    public static function defaultUrlSuffix(): ?string
    {
        return env('CMS_URL_PAGES_SUFFIX');
    }

    public function isActive(): bool
    {
        return ($this->getStatus() === static::STATUS_ACTIVE)
            && ($this->getPublishedAt() <= time())
            && ($this->getExpiredAt() >= time());
    }

    public function media(string $type = null): RepositoryInterface
    {
        $repository = $this->belongsToMany(Media::class, PageMedia::class, 'page_id');
        if ($type) {
            $repository->where('type', $type);
        }
        return $repository;
    }

    public function tags(): RepositoryInterface
    {
        return $this->belongsToMany(Tag::class, PageTag::class, 'page_id');
    }

    /**
     * @return PageWidget[]
     */
    public function widgets(): RepositoryInterface
    {
        return $this->belongsToMany(Widget::class, PageWidget::class, 'page_id')
            ->orderBy('R.sort_order');
    }

    public function layout(): ?Layout
    {
        if ($this->getLayoutId() === $this->getId()) {
            return Layout::fromPageEntity($this);
        }

        return $this->hasOne(Layout::class, static::column_primary_key());
    }

}