<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Page;

use Autumn\Database\Attributes\Index;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Extensions\Cms\Models\Meta\MetaEntity;
use Autumn\Extensions\Cms\Models\Meta\MetaManagerTrait;
use Autumn\Extensions\Cms\Models\Traits\PageIdColumnTrait;

#[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE, self::RELATION_PRIMARY_COLUMN, 'name', 'lang', 'code')]
class PageMeta extends MetaEntity implements RepositoryInterface
{
    use MetaManagerTrait;
    use PageIdColumnTrait;

    public const ENTITY_NAME = 'cms_page_meta';
    public const RELATION_PRIMARY_CLASS = Page::class;
    public const RELATION_PRIMARY_COLUMN = 'page_id';
}