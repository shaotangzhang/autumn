<?php
/**
 * Autumn PHP Framework
 *
 * Date:        15/05/2024
 */

namespace Autumn\Extensions\Cms\Models\Post;

use Autumn\Extensions\Cms\Models\Comment\CommentEntity;

class PostComment extends CommentEntity
{

    public const ENTITY_NAME = 'cms_post_comments';
    public const RELATION_PRIMARY_CLASS = Post::class;
    public const RELATION_PRIMARY_COLUMN = 'post_id';
}