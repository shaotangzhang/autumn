<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/7
 */

namespace App\Services\Security;

use App\Services\AbstractService;
use Autumn\Security\Authentication\UsernamePasswordAuthenticationToken;
use Autumn\Security\Exceptions\UsernameNotFoundException;
use Autumn\Security\Interfaces\UserDetailsServiceInterface;

class SecurityUserDetailsService extends AbstractService implements UserDetailsServiceInterface
{
    /**
     * @throws UsernameNotFoundException
     */
    public function loadUserByUsername(string $username): UsernamePasswordAuthenticationToken
    {
        if (strpos($username, '@')) {
            $field = 'email';
        } else {
            $field = 'username';
        }

        SecurityUserDetails::withoutTrashed()
            ->with('siteId', $this->getSiteId());

        if ($userDetails = SecurityUserDetails::findBy($field, $username)) {
            return new UsernamePasswordAuthenticationToken(
                $userDetails, null, ...$this->loadUserAuthorities($userDetails)
            );
        }

        throw new UsernameNotFoundException('Invalid username or password.');
    }

    public function loadUserAuthorities(SecurityUserDetails $userDetails): array
    {
        return [];
    }
}