<?php

namespace Autumn\Extensions\Auth\Interfaces;

use Autumn\Interfaces\ArrayInterface;

interface CredentialInterface extends ArrayInterface, \JsonSerializable, \Stringable
{
    /**
     * Get the access token.
     *
     * @return string
     */
    public function getAccessToken(): string;

    /**
     * Get the refresh token.
     *
     * @return string
     */
    public function getRefreshToken(): string;

    /**
     * Get the expiration time in seconds.
     *
     * @return int
     */
    public function getExpiresIn(): int;
}
