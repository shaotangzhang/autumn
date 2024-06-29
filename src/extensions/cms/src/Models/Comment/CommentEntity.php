<?php
/**
 * Autumn PHP Framework
 *
 * Date:        15/05/2024
 */

namespace Autumn\Extensions\Cms\Models\Comment;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Interfaces\Extendable;
use Autumn\Database\Interfaces\StatusInterface;
use Autumn\Database\Models\RecyclableEntity;
use Autumn\Database\Traits\ExtendedEntityTrait;
use Autumn\Database\Traits\StatusColumnTrait;
use Autumn\Extensions\Auth\Models\Traits\UserIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\LikeCountColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\PostIdColumnTrait;

class CommentEntity extends RecyclableEntity implements StatusInterface, Extendable
{
    use ExtendedEntityTrait;
    use PostIdColumnTrait;
    use UserIdColumnTrait;
    use LikeCountColumnTrait;
    use StatusColumnTrait;

    public const ENTITY_NAME = 'cms_comments';
    public const RELATION_PRIMARY_CLASS = null;
    public const RELATION_PRIMARY_COLUMN = null;

    #[Column(type: Column::TYPE_LONG_TEXT, name: 'message')]
    private string $message = '';

    #[Column(type: Column::TYPE_INT, name: 'reply_count', unsigned: true)]
    private int $replyCount = 0;

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
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
}