<?php
/**
 * Autumn PHP Framework
 *
 * Date:        9/05/2024
 */

namespace Autumn\Extensions\Auth\Interfaces;

interface UserDetailsInterface
{
    public function getAuthorities(): array;

    public function getPassword(): string;

    public function getUsername(): string;

    public function isAccountNonExpired(): bool;

    public function isAccountNonLocked(): bool;

    public function isCredentialsNonExpired(): bool;

    public function isEnabled(): bool;
}
