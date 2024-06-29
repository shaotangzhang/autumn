<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Category;

use Autumn\Extensions\Cms\Models\Page\PageEntity;
use Autumn\Extensions\Cms\Models\Traits\SiteIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\SlugifyUrlTrait;
use Autumn\Attributes\Transient;
use Autumn\Database\Attributes\Index;

#[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE, 'site_id', 'parent_id', 'type', 'slug', 'lang')]
class CategoryEntity extends PageEntity
{
    use SiteIdColumnTrait;

    public const ENTITY_NAME = 'cms_categories';

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
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    public function hasItems(): int
    {
        return count($this->items);
    }

    public static function defaultUrlPrefix(): ?string
    {
        return env('CMS_URL_CATEGORIES_PREFIX', '/categories/');
    }

    public static function defaultUrlSuffix(): ?string
    {
        return env('CMS_URL_CATEGORIES_SUFFIX');
    }
}