<?php

namespace Autumn\Extensions\Auth\Interfaces;

interface AuthSessionInterface
{
    /**
     * Get the unique identifier for the user.
     *
     * @return int
     */
    public function getUserId(): int;                               // user id

    /**
     * Get the username of the user
     *
     * @return string
     */
    public function getUsername(): string;

    /**
     * Get the roles assigned to the user.
     *
     * @return array
     */
    public function getRoles(): array;                             // roles

    /**
     * Get the authorities of the user
     *
     * @return array
     */
    public function getAuthorities(): array;

    /**
     * Get the authentication token for the user session.
     *
     * @return string
     */
    public function getAuthToken(): string;                         // token

    /**
     * Get the user's preferred language/locale.
     *
     * @return string|null
     */
    public function getLocale(): ?string;                            // locale

    /**
     * Get basic profile information of the user.
     *
     * @return array
     */
    public function getProfile(): array;                            // user entity

    /**
     * Get user preferences and settings.
     *
     * @return array
     */
    public function getPreferences(): array;                        // user meta

    /**
     * Get security context information.
     *
     * @return array
     */
    public function getSecurityContext(): array;                    // context

    /**
     * Get the timestamp of the last activity.
     *
     * @return \DateTime
     */
    public function getLastActivity(): \DateTimeInterface;          // updated_at

    /**
     * Get the session expiration time or duration.
     *
     * @return \DateTime
     */
    public function getSessionExpiration(): \DateTimeInterface;     // expired_at
}

