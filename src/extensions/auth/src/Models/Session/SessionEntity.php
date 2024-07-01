<?php

namespace Autumn\Extensions\Auth\Models\Session;

use Autumn\Attributes\Transient;
use Autumn\Database\Attributes\Column;
use Autumn\Database\Interfaces\Expirable;
use Autumn\Database\Models\RecyclableEntity;
use Autumn\Extensions\Auth\Models\Traits\IPv4ColumnTrait;
use Autumn\Extensions\Auth\Models\Traits\UserIdColumnTrait;

/**
 * Class SessionEntity
 *
 * Represents a session entity with session data management.
 */
class SessionEntity extends RecyclableEntity implements Expirable
{
    use UserIdColumnTrait;
    use IPv4ColumnTrait;

    public const ENTITY_NAME = 'auth_sessions';

    #[Column(type: Column::TYPE_CHAR, name: 'token', size: 32, charset: Column::CHARSET_ASCII)]
    private string $authToken = '';

    #[Column(type: Column::TYPE_CHAR, name: 'sid', size: 32, charset: Column::CHARSET_ASCII)]
    private string $sessionId = '';

    #[Column(type: Column::TYPE_JSON, name: 'data')]
    private ?string $sessionData = null;

    #[Transient]
    private ?array $session = null;

    /**
     * @return string
     */
    public function getAuthToken(): string
    {
        return $this->authToken;
    }

    /**
     * @param string $authToken
     */
    public function setAuthToken(string $authToken): void
    {
        $this->authToken = $authToken;
    }

    /**
     * Get the session ID.
     *
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * Set the session ID.
     *
     * @param string $sessionId
     */
    public function setSessionId(string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    /**
     * Get the session data as JSON string.
     *
     * @return string|null
     */
    public function getSessionData(): ?string
    {
        if ($this->sessionData === null) {
            return $this->sessionData = json_encode($this->session);
        }

        return $this->sessionData;
    }

    /**
     * Set the session data.
     *
     * @param string|null $sessionData
     */
    public function setSessionData(?string $sessionData): void
    {
        $this->sessionData = $sessionData;
        $this->session = null;
    }

    /**
     * Access the session data.
     *
     * @param string|null $key
     * @param mixed $value
     * @return mixed
     */
    public function session(string $key = null, mixed $value = null): mixed
    {
        $this->session ??= $this->sessionData ? json_decode($this->sessionData, true) : [];

        switch (func_num_args()) {
            case 0:
                return $this->session;

            case 1:
                return $this->session[$key] ?? null;

            default:
                $this->sessionData = null;
                return $this->session[$key] = $value;
        }
    }

    public static function column_expired_at(): string
    {
        return static::column_deleted_at();
    }

    public function isExpired(): bool
    {
        return $this->isTrashed();
    }

    public function getExpiredAt(): int
    {
        return $this->getDeletedAt();
    }

    public function getExpiryTime(): ?\DateTimeInterface
    {
        return $this->getDeleteTime();
    }
}
