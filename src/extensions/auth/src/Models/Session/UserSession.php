<?php

namespace Autumn\Extensions\Auth\Models\Session;

use Autumn\Attributes\Transient;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Traits\RecyclableEntityManagerTrait;
use Autumn\Extensions\Auth\Interfaces\AuthSessionInterface;
use Autumn\Extensions\Auth\Interfaces\UserDetailsInterface;
use Autumn\Extensions\Auth\Models\User\User;
use Autumn\Extensions\Auth\Models\User\UserDetails;
use Autumn\Interfaces\ArrayInterface;
use Autumn\System\Session;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class UserSession
 *
 * Represents an authenticated session entity with extended session management.
 */
class UserSession extends SessionEntity implements AuthSessionInterface, RepositoryInterface
{
    use RecyclableEntityManagerTrait;

    #[Transient]
    private UserDetailsInterface $userDetails;

    /**
     * Load an authenticated session from the session storage.
     *
     * @param string $key
     * @return static|null
     * @throws InvalidArgumentException
     */
    public static function loadFromSession(string $key): ?static
    {
        $data = Session::get($key);
        if ($data instanceof static) {
            $reflection = new \ReflectionObject($data);
            $property = $reflection->getProperty('userDetails');
            if (!$property->isInitialized()) {
                if ($userId = $data->getUserId()) {
                    if ($user = User::find($userId)) {
                        $data->userDetails = UserDetails::fromUserEntity($user);
                        return $data;
                    }
                }
                $data->userDetails = new UserDetails;
            }
            return $data;
        }
        return null;
    }

    /**
     * Get the user details associated with the session.
     *
     * @return UserDetailsInterface
     */
    public function getUserDetails(): UserDetailsInterface
    {
        return $this->userDetails;
    }

    /**
     * Set the user details associated with the session.
     *
     * @param UserDetailsInterface $userDetails
     */
    public function setUserDetails(UserDetailsInterface $userDetails): void
    {
        $this->userDetails = $userDetails;
    }

    /**
     * Get the username of the authenticated user.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->userDetails->getUsername();
    }

    /**
     * Get the authorities (roles and permissions) of the authenticated user.
     *
     * @return array
     */
    public function getAuthorities(): array
    {
        return $this->userDetails->getAuthorities();
    }

    /**
     * Get the locale preference of the authenticated user.
     *
     * @return string|null
     */
    public function getLocale(): ?string
    {
        return $this->getPreferences()['locale'] ?? null;
    }

    /**
     * Set the locale preference of the authenticated user.
     *
     * @param string|null $locale
     */
    public function setLocale(?string $locale): void
    {
        $preferences = $this->getPreferences();
        $preferences['locale'] = $locale;
        $this->setPreferences($preferences);
    }

    /**
     * Get the roles assigned to the authenticated user.
     *
     * @return array
     */
    public function getRoles(): array
    {
        $data = $this->session('roles');
        if (is_array($data)) {
            return $data;
        }
        return [];
    }

    /**
     * Set the roles for the authenticated user.
     *
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->session('roles', $roles);
    }

    /**
     * Get the profile data of the authenticated user.
     *
     * @return array
     */
    public function getProfile(): array
    {
        $data = $this->session('profile');
        if (is_array($data)) {
            return $data;
        }
        return [];
    }

    /**
     * Set the profile data for the authenticated user.
     *
     * @param array|ArrayInterface $profile
     */
    public function setProfile(array|ArrayInterface $profile): void
    {
        if ($profile instanceof ArrayInterface) {
            $profile = $profile->toArray();
        }
        unset($profile['password']);
        $this->session('profile', $profile);
    }

    /**
     * Get the preferences of the authenticated user.
     *
     * @return array
     */
    public function getPreferences(): array
    {
        $data = $this->session('preferences');
        if (is_array($data)) {
            return $data;
        }
        return [];
    }

    /**
     * Set the preferences for the authenticated user.
     *
     * @param array|ArrayInterface $preferences
     */
    public function setPreferences(array|ArrayInterface $preferences): void
    {
        if ($preferences instanceof ArrayInterface) {
            $this->session('preferences', $preferences->toArray());
        } else {
            $this->session('preferences', $preferences);
        }
    }

    /**
     * Get the security context of the authenticated user.
     *
     * @return array
     */
    public function getSecurityContext(): array
    {
        $data = $this->session('securityContext');
        if (is_array($data)) {
            return $data;
        }
        return [];
    }

    /**
     * Set the security context for the authenticated user.
     *
     * @param array|ArrayInterface $context
     */
    public function setSecurityContext(array|ArrayInterface $context): void
    {
        if ($context instanceof ArrayInterface) {
            $this->session('securityContext', $context->toArray());
        } else {
            $this->session('securityContext', $context);
        }
    }

    /**
     * Get the last activity time of the authenticated session.
     *
     * @return \DateTimeInterface
     */
    public function getLastActivity(): \DateTimeInterface
    {
        return $this->getUpdateTime() ?? $this->getCreateTime();
    }

    /**
     * Set the last activity time of the authenticated session.
     *
     * @param int|float|string|\DateTimeInterface $time
     */
    public function setLastActivity(int|float|string|\DateTimeInterface $time): void
    {
        $this->setUpdatedAt($time);
    }

    /**
     * Get the expiration time of the authenticated session.
     *
     * @return \DateTimeInterface
     */
    public function getSessionExpiration(): \DateTimeInterface
    {
        return $this->getExpiryTime();
    }

    /**
     * Set the expiration time of the authenticated session.
     *
     * @param int|float|string|\DateTimeInterface $time
     */
    public function setSessionExpiration(int|float|string|\DateTimeInterface $time): void
    {
        $this->setDeletedAt($time);
    }
}
