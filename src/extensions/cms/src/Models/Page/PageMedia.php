<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Page;

use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Interfaces\TypeInterface;
use Autumn\Database\Models\Relation;
use Autumn\Database\Traits\PrimaryIdColumnTrait;
use Autumn\Database\Traits\RelationManagerTrait;
use Autumn\Database\Traits\TypeColumnTrait;
use Autumn\Extensions\Cms\Interfaces\Sortable;
use Autumn\Extensions\Cms\Models\Media\Media;
use Autumn\Extensions\Cms\Models\Media\MediaRelation;
use Autumn\Extensions\Cms\Models\Traits\MediaIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\PageIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\SortOrderColumnTrait;

class PageMedia extends MediaRelation
{
    use PageIdColumnTrait;
    use RelationManagerTrait;

    public const ENTITY_NAME = 'cms_page_media';

    public const RELATION_PRIMARY_COLUMN = 'page_id';

    public const RELATION_PRIMARY_CLASS = Page::class;
}