<?php

namespace Autumn\Extensions\Cms\Models\Message;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Interfaces\StatusInterface;
use Autumn\Database\Interfaces\TypeInterface;
use Autumn\Database\Models\RecyclableEntity;
use Autumn\Database\Traits\DescriptionColumnTrait;
use Autumn\Database\Traits\StatusColumnTrait;
use Autumn\Database\Traits\TypeColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\ContentColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\TitleColumnTrait;
use Autumn\Lang\HTML;

class MessageEntity extends RecyclableEntity implements TypeInterface, StatusInterface
{
    use TitleColumnTrait;
    use DescriptionColumnTrait;
    use ContentColumnTrait;
    use TypeColumnTrait;
    use StatusColumnTrait;

    public const ENTITY_NAME = 'cms_messages';
    public const DEFAULT_TYPE = 'default';
    public const DEFAULT_STATUS = 'pending';    // pending, active: unread, disabled: read, trashed

    #[Column(type: Column::FK, name: 'sender_id')]
    private int $senderId = 0;      // sender's user_id
    #[Column(type: Column::FK, name: 'receiver_id')]
    private int $receiverId = 0;    // receiver's user_id
    #[Column(type: Column::FK, name: 'guest_id')]
    private int $guestId = 0;

    #[Column(type: Column::TYPE_INT, name: 'attachment_count', unsigned: true)]
    private int $attachmentCount = 0;
    #[Column(type: Column::TYPE_BOOL, name: 'important')]
    private bool $important = false;
    #[Column(type: Column::TYPE_BOOL, name: 'urgent')]
    private bool $urgent = false;
    #[Column(type: Column::TYPE_BOOL, name: 'sticky')]
    private bool $sticky = false;
    #[Column(type: Column::TYPE_BOOL, name: 'spam')]
    private bool $spam = false;

    public function getSubject(): string
    {
        return $this->getTitle();
    }

    public function setSubject(string $title): void
    {
        $this->setTitle($title);
    }

    public function getMessage(): ?string
    {
        return $this->getContent();
    }

    public function setMessage(?string $content): void
    {
        $this->setContent($content);
    }

    public function getTextContent(): ?string
    {
        $textContent = $this->getDescription();
        if (isset($textContent)) {
            return $textContent;
        }

        $textContent = HTML::stripTags($this->getContent());
        $this->setDescription($textContent);
        return $textContent;
    }

    public function setTextContent(?string $content): void
    {
        $this->setDescription($content);
    }

    /**
     * @return int
     */
    public function getSenderId(): int
    {
        return $this->senderId;
    }

    /**
     * @param int $senderId
     */
    public function setSenderId(int $senderId): void
    {
        $this->senderId = $senderId;
    }

    /**
     * @return int
     */
    public function getReceiverId(): int
    {
        return $this->receiverId;
    }

    /**
     * @param int $receiverId
     */
    public function setReceiverId(int $receiverId): void
    {
        $this->receiverId = $receiverId;
    }

    /**
     * @return int
     */
    public function getGuestId(): int
    {
        return $this->guestId;
    }

    /**
     * @param int $guestId
     */
    public function setGuestId(int $guestId): void
    {
        $this->guestId = $guestId;
    }

    public function isImportant(): bool
    {
        return $this->important;
    }

    public function setImportant(bool $important): void
    {
        $this->important = $important;
    }

    public function isUrgent(): bool
    {
        return $this->urgent;
    }

    public function setUrgent(bool $urgent): void
    {
        $this->urgent = $urgent;
    }

    public function isSticky(): bool
    {
        return $this->sticky;
    }

    public function setSticky(bool $sticky): void
    {
        $this->sticky = $sticky;
    }

    public function isSpam(): bool
    {
        return $this->spam;
    }

    public function setSpam(bool $spam): void
    {
        $this->spam = $spam;
    }

    public function getAttachmentCount(): int
    {
        return $this->attachmentCount;
    }

    public function setAttachmentCount(int $attachmentCount): void
    {
        $this->attachmentCount = $attachmentCount;
    }
}