<?php
/**
 * Autumn PHP Framework
 *
 * Date:        12/06/2024
 */

namespace Autumn\Extensions\Cms\Models\Guess;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;
use Autumn\Database\Models\RecyclableEntity;
use Autumn\Extensions\Cms\Models\Traits\ConfigColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\EmailColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\NicknameColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\PhoneNumberColumnTrait;

#[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE, 'nonce', 'signature')]
class GuestEntity extends RecyclableEntity
{
    use NicknameColumnTrait;
    use EmailColumnTrait;
    use PhoneNumberColumnTrait;
    use ConfigColumnTrait;

    public const ENTITY_NAME = 'cms_guests';

    #[Column(type: Column::TYPE_CHAR, name: 'nonce', size: 32, charset: Column::CHARSET_ASCII)]
    private string $nonce = '';
    #[Column(type: Column::TYPE_CHAR, name: 'signature', size: 32, charset: Column::CHARSET_ASCII)]
    private string $signature = '';
    private string $phoneNumber = '';

    /**
     * IPv4
     * @var string|null
     */
    #[Column(type: Column::TYPE_CHAR, name: 'ip', size: 15, charset: Column::CHARSET_ASCII)]
    private ?string $ip = null;

    #[Column(type: Column::TYPE_TEXT, name: 'user_agent', charset: Column::CHARSET_ASCII)]
    private ?string $userAgent = null;

    public function getNonce(): string
    {
        return $this->nonce;
    }

    public function setNonce(string $nonce): void
    {
        $this->nonce = $nonce;
    }

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function setSignature(string $signature): void
    {
        $this->signature = $signature;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): void
    {
        $this->ip = $ip;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }
}