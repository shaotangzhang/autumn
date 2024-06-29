<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Category;

use Autumn\Database\Traits\RelationManagerTrait;
use Autumn\Extensions\Cms\Models\Media\MediaRelation;
use Autumn\Extensions\Cms\Models\Traits\CategoryIdColumnTrait;

class CategoryMedia extends MediaRelation
{
    use RelationManagerTrait;
    use CategoryIdColumnTrait;

    public const ENTITY_NAME = 'cms_category_media';

    public const RELATION_PRIMARY_COLUMN = 'category_id';

    public const RELATION_PRIMARY_CLASS = Category::class;
}