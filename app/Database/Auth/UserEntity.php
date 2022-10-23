<?php
/**
 * Enflares PHP Framework
 *
 * Date:        2022/9/28
 */

namespace App\Database\Auth;

use Autumn\Database\AbstractEntity;
use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;

class UserEntity extends AbstractEntity
{
    public const ENTITY_NAME = 'auth_users';

    public const TYPE_PENDING = 'pending';
    public const TYPE_ACTIVE = 'active';
    public const TYPE_DISABLED = 'disabled';
    public const DEFAULT_TYPE = self::TYPE_PENDING;

    #[Column(type: 'bigint')]
    private int $siteId = 0;

    #[Column(size: 40, collation: 'ascii_general_ci')]
    #[Index(index: true, unique: true)]
    private string $username = '';

    #[Column(collation: 'ascii_general_ci')]
    private string $password = '';

    private string $email = '';

    #[Column(size: 100)]
    private ?string $nickname = null;

    private ?string $avatar = null;

    private ?string $link = null;

    #[Column(type: 'text')]
    private ?string $description = null;

    #[Column(type: 'char', size: 10, collation: 'ascii_general_ci')]
    #[Index]
    private string $type = 'standard';

    #[Column(type: 'char', size: 10, collation: 'ascii_general_ci')]
    #[Index]
    private string $status = 'pending';

    #[Column(type: 'char', size: 5, collation: 'ascii_general_ci')]
    #[Index]
    private string $lang = '';

    #[Column(type: 'char', size: 15, collation: 'ascii_general_ci')]
    #[Index]
    private string $ip = '';

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
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


}