<?php
/**
 * Enflares PHP Framework
 *
 * Date:        2022/9/30
 */

namespace App\Database\Blog;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;
use Autumn\Database\RecyclableEntity;
use Autumn\Lang\Date;
use DateTime;

abstract class AbstractArticle extends RecyclableEntity
{
    public const DEFAULT_TYPE = 'standard';
    public const DEFAULT_STATUS = 'pending';
    public const DEFAULT_SHARE = 'public';

    public const STATUS_ACTIVE = 'published';
    public const STATUS_DISABLED = 'disabled';

    #[Column(type: 'bigint')]
    #[Index(index: true, unique: true)]
    #[Index('i_slug')]
    private int $parentId = 0;

    #[Column]
    private string $title = '';

    #[Column(type: 'text')]
    private ?string $description = null;

    #[Column(type: 'longtext')]
    private ?string $content = null;

    #[Column]
    private ?string $image = null;

    #[Column]
    private ?string $video = null;

    #[Column]
    private ?string $link = null;

    #[Column]
    #[Index('i_slug')]
    #[Index(index: true, unique: true)]
    private ?string $slug = null;

    #[Column(size: 32, collation: 'ascii_general_ci')]
    private ?string $password = null;

    #[Column(size: 20, collation: 'ascii_general_ci')]
    #[Index(index: true, unique: true)]
    #[Index('i_slug')]
    private string $type;

    #[Column(type: 'char', size: 10, collation: 'ascii_general_ci')]
    #[Index]
    #[Index('i_slug')]
    private string $status;

    #[Column(type: 'char', size: 10, collation: 'ascii_general_ci')]
    #[Index]
    private string $share;

    #[Column(type: 'char', size: 5, collation: 'ascii_general_ci')]
    #[Index(index: true, unique: true)]
    #[Index('i_slug')]
    private string $lang = '';

    private int $sortOrder = 0;

    private int $itemCount = 0;

    private int $likeCount = 0;

    private int $visitCount = 0;

    private int $commentCount = 0;

    #[Column(type: 'int', unsigned: true)]
    #[Index]
    private int $publishedAt = 0;

    #[Column(type: 'int', unsigned: true)]
    #[Index]
    private int $expiredAt = 0;

    #[Column(type: 'int', unsigned: true)]
    #[Index]
    private int $approvedAt = 0;

    public function __construct()
    {
        $this->type = static::DEFAULT_TYPE;
        $this->share = static::DEFAULT_SHARE;
        $this->status = static::DEFAULT_STATUS;
    }

    /**
     * @return int
     */
    public function getParentId(): int
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId
     */
    public function setParentId(int $parentId): void
    {
        $this->parentId = $parentId;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @param string|null $image
     */
    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    /**
     * @return string|null
     */
    public function getVideo(): ?string
    {
        return $this->video;
    }

    /**
     * @param string|null $video
     */
    public function setVideo(?string $video): void
    {
        $this->video = $video;
    }

    /**
     * @return string|null
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * @param string|null $link
     */
    public function setLink(?string $link): void
    {
        $this->link = $link;
    }

    /**
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string|null $slug
     */
    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getShare(): string
    {
        return $this->share;
    }

    /**
     * @param string $share
     */
    public function setShare(string $share): void
    {
        $this->share = $share;
    }

    /**
     * @return string
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * @param string $lang
     */
    public function setLang(string $lang): void
    {
        $this->lang = $lang;
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     */
    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * @return int
     */
    public function getItemCount(): int
    {
        return $this->itemCount;
    }

    /**
     * @param int $itemCount
     */
    public function setItemCount(int $itemCount): void
    {
        $this->itemCount = $itemCount;
    }

    /**
     * @return int
     */
    public function getLikeCount(): int
    {
        return $this->likeCount;
    }

    /**
     * @param int $likeCount
     */
    public function setLikeCount(int $likeCount): void
    {
        $this->likeCount = $likeCount;
    }

    /**
     * @return int
     */
    public function getVisitCount(): int
    {
        return $this->visitCount;
    }

    /**
     * @param int $visitCount
     */
    public function setVisitCount(int $visitCount): void
    {
        $this->visitCount = $visitCount;
    }

    public function isCommentDisabled(): bool
    {
        return $this->commentCount < 0;
    }

    /**
     * @return int
     */
    public function getCommentCount(): int
    {
        return $this->commentCount;
    }

    /**
     * @param int $commentCount
     */
    public function setCommentCount(int $commentCount): void
    {
        $this->commentCount = $commentCount;
    }

    /**
     * @return int
     */
    public function getPublishedAt(): int
    {
        return $this->publishedAt;
    }

    /**
     * @param int|string|DateTime $publishedAt
     */
    public function setPublishedAt(int|string|DateTime $publishedAt): void
    {
        $this->publishedAt = Date::time($publishedAt) ?: 0;
    }

    /**
     * @return int
     */
    public function getExpiredAt(): int
    {
        return $this->expiredAt;
    }

    /**
     * @param int|string|DateTime $expiredAt
     */
    public function setExpiredAt(int|string|DateTime $expiredAt): void
    {
        $this->expiredAt = Date::time($expiredAt) ?: 0;
    }

    /**
     * @return int
     */
    public function getApprovedAt(): int
    {
        return $this->approvedAt;
    }

    /**
     * @param int|string|DateTime $approvedAt
     */
    public function setApprovedAt(int|string|DateTime $approvedAt): void
    {
        $this->approvedAt = Date::time($approvedAt) ?: 0;
    }


}