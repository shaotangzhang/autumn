<?php

namespace Autumn\Extensions\Auth\Models\Credentials;

use Autumn\Extensions\Auth\Interfaces\CredentialInterface;
use Autumn\Extensions\Auth\Models\Session\UserSession;

class DefaultCredential implements CredentialInterface
{
    private string $accessToken;
    private string $refreshToken;
    private int $expiresIn;

    public function __construct(string $accessToken, string $refreshToken, int $expiresIn)
    {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->expiresIn = $expiresIn;
    }

    public static function fromUserSession(UserSession $session): static
    {
        return new static(
            $session->getAuthToken(),
            $session->getSessionId(),
            $session->getSessionExpiration()->getTimestamp() - time()
        );
    }

    public function __toString(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     */
    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    /**
     * @param int $expiresIn
     */
    public function setExpiresIn(int $expiresIn): void
    {
        $this->expiresIn = $expiresIn;
    }

    public function toArray(): array
    {
        return [
            'access_token' => $this->getAccessToken(),
            'refresh_token' => $this->getRefreshToken(),
            'expires_in' => $this->getExpiresIn(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
