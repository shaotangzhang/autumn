<?php
/**
 * Enflares PHP Framework
 *
 * Date:        2022/9/30
 */

namespace App\Database\Blog;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;

class SiteEntity extends AbstractArticle
{
    public const ENTITY_NAME = 'blog_sites';

    #[Column(type: 'bigint')]
    #[Index(index: true, unique: true)]
    #[Index('i_slug')]
    private int $userId = 0;
}