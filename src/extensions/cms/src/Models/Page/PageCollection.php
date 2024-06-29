<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Page;

use Autumn\Database\Traits\RelationManagerTrait;
use Autumn\Extensions\Cms\Models\Collection\CollectionRelation;
use Autumn\Extensions\Cms\Models\Traits\PageIdColumnTrait;

class PageCollection extends CollectionRelation
{
    use PageIdColumnTrait;
    use RelationManagerTrait;

    public const ENTITY_NAME = 'cms_page_collections';
    public const RELATION_PRIMARY_COLUMN = 'page_id';
    public const RELATION_PRIMARY_CLASS = Page::class;

}