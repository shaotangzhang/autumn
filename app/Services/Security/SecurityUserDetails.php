<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/7
 */

namespace App\Services\Security;

use App\Database\Auth\UserEntity;
use Autumn\Security\Interfaces\UserDetails;

class SecurityUserDetails extends UserEntity implements UserDetails
{
    public function getAuthorities(): array
    {
        return [];
    }

    public function isEnabled(): bool
    {
        return $this->getStatus() === static::TYPE_ACTIVE;
    }

    public function isAccountNonExpired(): bool
    {
        return !$this->isTrashed();
    }

    public function isAccountNonLocked(): bool
    {
        return $this->getStatus() !== static::TYPE_DISABLED;
    }

    public function isCredentialsNonExpired(): bool
    {
        return true;
    }
}