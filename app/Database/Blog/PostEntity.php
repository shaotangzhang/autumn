<?php
/**
 * Enflares PHP Framework
 *
 * Date:        2022/9/30
 */

namespace App\Database\Blog;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;

class PostEntity extends AbstractArticle
{
    public const ENTITY_NAME = 'blog_posts';
    public const STATUS_ACTIVE = 'published';
    public const STATUS_DISABLED = 'disabled';

    #[Column(type: 'bigint')]
    #[Index(index: true, unique: true)]
    #[Index('i_slug')]
    private int $siteId = 0;

    #[Column(type: 'bigint')]
    #[Index(index: true, unique: true)]
    #[Index('i_slug')]
    private int $userId = 0;

    /**
     * @return int
     */
    public function getSiteId(): int
    {
        return $this->siteId;
    }

    /**
     * @param int $siteId
     */
    public function setSiteId(int $siteId): void
    {
        $this->siteId = $siteId;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }


}