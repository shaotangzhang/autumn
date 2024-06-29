<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Post;

use Autumn\Database\Attributes\Index;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Extensions\Cms\Models\Meta\MetaEntity;
use Autumn\Extensions\Cms\Models\Meta\MetaManagerTrait;
use Autumn\Extensions\Cms\Models\Traits\PostIdColumnTrait;

#[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE, self::RELATION_PRIMARY_COLUMN, 'name', 'lang', 'code')]
class PostMeta extends MetaEntity implements RepositoryInterface
{
    use PostIdColumnTrait;
    use MetaManagerTrait;

    public const ENTITY_NAME = 'cms_post_meta';
    public const RELATION_PRIMARY_CLASS = Post::class;
    public const RELATION_PRIMARY_COLUMN = 'post_id';
}