<?php
namespace Autumn\Extensions\Auth\Models\Session;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Interfaces\Expirable;
use Autumn\Database\Models\Entity;
use Autumn\Database\Traits\CreatableTrait;
use Autumn\Database\Traits\ExpirableTrait;
use Autumn\Extensions\Auth\Models\Traits\IPv4ColumnTrait;
use Autumn\Extensions\Auth\Models\Traits\UserIdColumnTrait;

class SessionEntity extends Entity implements Expirable
{
    use UserIdColumnTrait;
    use IPv4ColumnTrait;
    use CreatableTrait;
    use ExpirableTrait;

    public const ENTITY_NAME = 'auth_sessions';

    #[Column(type: Column::TYPE_CHAR, name: 'sid', size: 32, charset: Column::CHARSET_ASCII)]
    private string $sessionId = '';

    #[Column(type: Column::TYPE_BLOB, name: 'data')]
    private ?string $sessionData = null;

    /**
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     */
    public function setSessionId(string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @return string|null
     */
    public function getSessionData(): ?string
    {
        return $this->sessionData;
    }

    /**
     * @param string|null $sessionData
     */
    public function setSessionData(?string $sessionData): void
    {
        $this->sessionData = $sessionData;
    }
}