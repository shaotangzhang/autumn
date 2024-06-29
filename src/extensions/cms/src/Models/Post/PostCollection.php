<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Post;

use Autumn\Database\Traits\RelationManagerTrait;
use Autumn\Extensions\Cms\Models\Collection\CollectionRelation;
use Autumn\Extensions\Cms\Models\Traits\PostIdColumnTrait;

class PostCollection extends CollectionRelation
{
    use PostIdColumnTrait;
    use RelationManagerTrait;

    public const ENTITY_NAME = 'cms_post_collections';
    public const RELATION_PRIMARY_COLUMN = 'post_id';
    public const RELATION_PRIMARY_CLASS = Post::class;
}