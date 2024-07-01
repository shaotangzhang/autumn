<?php

namespace Autumn\Extensions\Auth\Models\User;

use Autumn\Attributes\JsonIgnore;
use Autumn\Exceptions\AuthenticationException;
use Autumn\Extensions\Auth\Interfaces\UserDetailsInterface;

class UserDetails implements UserDetailsInterface
{
    private string $username = '';

    #[JsonIgnore]
    private string $password = '';
    private bool $enabled = false;
    private bool $accountNonLocked = false;
    private bool $accountNonExpired = false;
    private bool $credentialsNonExpired = false;
    private array $authorities = [];

    public static function fromUserEntity(UserEntity $user, array $authorities = null): static
    {
        $instance = new static;

        $instance->username = $user->getUsername();
        $instance->password = $user->getPassword();
        $instance->enabled = $user->getStatus() === $user::STATUS_ACTIVE;
        $instance->accountNonLocked = $user->getStatus() !== $user::STATUS_DISABLED;
        $instance->accountNonExpired = !$user->isTrashed() && !$user->isExpired();
        $instance->credentialsNonExpired = true;
        $instance->authorities = $authorities ?? [];

        return $instance;
    }

    public function isCredentialsNonExpired(): bool
    {
        return $this->credentialsNonExpired;
    }

    public function getAuthorities(): array
    {
        return $this->authorities;
    }

    /**
     * @param array $authorities
     */
    public function setAuthorities(array $authorities): void
    {
        $this->authorities = $authorities;
    }

    public function isAccountNonExpired(): bool
    {
        return $this->accountNonExpired;
    }

    public function isAccountNonLocked(): bool
    {
        return $this->accountNonLocked;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function verify(): static
    {
        if (!$this->isEnabled()) {
            throw AuthenticationException::of('The account is suspended or disabled.');
        }

        if (!$this->isAccountNonLocked()) {
            throw AuthenticationException::of('The account is locked.');
        }

        if (!$this->isAccountNonExpired()) {
            throw AuthenticationException::of('The account is expired.');
        }

        if (!$this->isCredentialsNonExpired()) {
            throw AuthenticationException::of('The credential is expired.');
        }

        return $this;
    }
}