<?php
namespace Autumn\Extensions\Cms\Models\Post;

use Autumn\Extensions\Cms\Interfaces\Categorized;
use Autumn\Extensions\Cms\Models\Page\PageEntity;
use Autumn\Extensions\Cms\Models\Traits\AuthorIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\CategoryIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\CommentCountColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\LikeCountColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\VisitCountColumnTrait;
use Autumn\Database\Attributes\Index;

#[Index('idx_category', Index::INDEX, 'category_id')]
class PostEntity extends PageEntity implements Categorized
{
    use CategoryIdColumnTrait;

    use AuthorIdColumnTrait;

    use LikeCountColumnTrait;

    use VisitCountColumnTrait;

    use CommentCountColumnTrait;

    public const ENTITY_NAME = 'cms_posts';

    public static function defaultUrlPrefix(): ?string
    {
        return env('CMS_URL_POSTS_PREFIX', '/posts/');
    }

    public static function defaultUrlSuffix(): ?string
    {
        return env('CMS_URL_POSTS_SUFFIX');
    }
}