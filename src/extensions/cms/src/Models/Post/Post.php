<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/02/2024
 */

namespace Autumn\Extensions\Cms\Models\Post;

use Autumn\Database\Interfaces\RecyclableRepositoryInterface;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Traits\RecyclableEntityManagerTrait;
use Autumn\Extensions\Cms\Models\Author\Author;
use Autumn\Extensions\Cms\Models\Traits\CategoryIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\MultipleSitesRepositoryTrait;

class Post extends PostEntity implements RecyclableRepositoryInterface
{
    use RecyclableEntityManagerTrait;

    use MultipleSitesRepositoryTrait;
    use CategoryIdColumnTrait;

    public function author(): ?Author
    {
        return $this->hasOne(Author::class, 'author_id');
    }

    public function comments(): RepositoryInterface
    {
        return $this->hasMany(PostComment::class)->orderBy('created_at', true);
    }
}