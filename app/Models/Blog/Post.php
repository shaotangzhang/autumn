<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/11
 */

namespace App\Models\Blog;

use App\Database\Blog\PostEntity;
use App\Models\Blog\Traits\PostTrait;

class Post extends PostEntity
{
    use PostTrait;

    public const STATUS_ACTIVE = 'published';
}