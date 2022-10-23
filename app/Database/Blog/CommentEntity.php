<?php
/**
 * Enflares PHP Framework
 *
 * Date:        2022/9/30
 */

namespace App\Database\Blog;

use DateTime;
use Autumn\Database\AbstractEntity;
use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;
use Autumn\Lang\Date;

class CommentEntity extends AbstractEntity
{
    public const ENTITY_NAME = 'blog_comments';

    public const DEFAULT_TYPE = 'standard';
    public const DEFAULT_STATUS = 'pending';

    public const STATUS_ACTIVE = 'approved';
    public const STATUS_DISABLED = 'disabled';

    #[Column(type: 'bigint')]
    #[Index(index: true, unique: true)]
    private int $postId = 0;

    #[Column(type: 'bigint')]
    #[Index(index: true, unique: true)]
    private int $userId = 0;

    #[Column(type: 'bigint')]
    #[Index(index: true, unique: true)]
    private int $parentId = 0;

    #[Column]
    private string $title = '';

    #[Column(type: 'text')]
    private ?string $description = null;

    #[Column(type: 'text')]
    private ?string $content = null;

    private ?string $email = '';

    private ?string $nickname = null;

    private ?string $avatar = null;

    private ?string $link = null;

    #[Column(size: 20, collation: 'ascii_general_ci')]
    #[Index]
    private string $type;

    #[Column(type: 'char', size: 10, collation: 'ascii_general_ci')]
    #[Index]
    private string $status;

    #[Column(type: 'char', size: 5, collation: 'ascii_general_ci')]
    #[Index]
    private string $lang = '';

    #[Column(type: 'char', size: 15, collation: 'ascii_general_ci')]
    #[Index]
    private string $ip = '';

    private int $sortOrder = 0;

    private int $itemCount = 0;

    private int $likeCount = 0;

    private int $replyCount = 0;

    #[Column(type: 'int', unsigned: true)]
    #[Index]
    private int $approvedAt = 0;

    public function __construct()
    {
        $this->type = static::DEFAULT_TYPE;
        $this->status = static::DEFAULT_STATUS;
    }

    /**
     * @return int
     */
    public function getPostId(): int
    {
        return $this->postId;
    }

    /**
     * @param int $postId
     */
    public function setPostId(int $postId): void
    {
        $this->postId = $postId;
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
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    /**
     * @param string|null $nickname
     */
    public function setNickname(?string $nickname): void
    {
        $this->nickname = $nickname;
    }

    /**
     * @return string|null
     */
    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    /**
     * @param string|null $avatar
     */
    public function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;
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
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp(string $ip): void
    {
        $this->ip = $ip;
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
    public function getReplyCount(): int
    {
        return $this->replyCount;
    }

    /**
     * @param int $replyCount
     */
    public function setReplyCount(int $replyCount): void
    {
        $this->replyCount = $replyCount;
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