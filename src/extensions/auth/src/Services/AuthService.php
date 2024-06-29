<?php

namespace Autumn\Extensions\Auth\Services;

use Autumn\Database\DbException;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Exceptions\AccessDeniedException;
use Autumn\Exceptions\AuthenticationException;
use Autumn\Exceptions\ConflictException;
use Autumn\Exceptions\ServerException;
use Autumn\Exceptions\ValidationException;
use Autumn\Extensions\Auth\Interfaces\UserDetailsInterface;
use Autumn\Extensions\Auth\Models\Role\Role;
use Autumn\Extensions\Auth\Models\Session\SessionEntity;
use Autumn\Extensions\Auth\Models\Session\UserSession;
use Autumn\Extensions\Auth\Models\User\User;
use Autumn\Extensions\Auth\Models\User\UserDetails;
use Autumn\Extensions\Auth\Models\User\UserEntity;
use Autumn\Extensions\Auth\Models\User\UserRole;
use Autumn\System\Session;

class AuthService extends AbstractService
{
    private ?UserEntity $currentUser = null;
    private ?UserDetailsInterface $currentUserDetails = null;

    /**
     * @return UserEntity|null
     */
    public function getCurrentUserInfo(): ?UserEntity
    {
        return $this->currentUser ??= Session::get('Auth::userInfo');
    }

    /**
     * @return UserDetailsInterface|null
     */
    public function getCurrentUserDetails(): ?UserDetailsInterface
    {
        return $this->currentUserDetails ??= Session::get('Auth::userDetails');
    }

    public function loadFromSession(): ?UserDetails
    {
        $userDetails = Session::get('Auth::userDetails');
        if ($userDetails instanceof UserDetailsInterface) {
            return $userDetails;
        }

        $userInfo = Session::get('Auth::userInfo');
        if ($userInfo instanceof UserEntity) {
            return UserDetails::fromUserEntity($userInfo);
        }

        return null;
    }

    public function hasRoles(string ...$roles): ?array
    {
        if ($userDetails = $this->currentUserDetails ??= $this->loadFromSession()) {

            $has = array_intersect($roles, $userDetails->getAuthorities());
            if (count($has)) {
                return $has;
            }

            if (in_array(Role::SUPERVISOR, $userDetails->getAuthorities())) {
                return $roles;
            }
        }

        return null;
    }


    public function isLogin(): bool
    {
        return ($this->getCurrentUserDetails()?->getUsername() !== null)
            || ($this->getCurrentUserInfo()?->getUsername() !== null);
    }

    public function logout(): void
    {
        $this->currentUser = $this->currentUserDetails = null;

        Session::remove('Auth::userDetails');
        Session::remove('Auth::userInfo');
    }

    public function login(string $username, string $password): UserDetailsInterface
    {
        $field = str_contains($username, '@') ? 'email' : 'username';

        $user = User::findBy([$field => $username])->withoutTrashed()->first();

        if (!$user || !$this->comparePassword($password, $user->getPassword())) {
            throw AuthenticationException::of('Invalid username or password.');
        }

        $this->logout();
        return $this->loginUserInfo($user);
    }

    public function loginUserInfo(UserEntity $user): UserDetailsInterface
    {
        $userDetails = UserDetails::fromUserEntity($user);

        if (!$userDetails->isAccountNonLocked()) {
            throw AuthenticationException::of('Account is locked.');
        }

        if (!$userDetails->isAccountNonExpired()) {
            throw AuthenticationException::of('Account is expired.');
        }

        if (!$userDetails->isEnabled()) {
            throw AuthenticationException::of('Account is not ready yet.');
        }

        $list = [];
        if ($user->getId() === 1) {
            $list = [Role::SUPERVISOR, Role::ADMIN];
        } else {
            foreach ($this->getUserPermissions($user) as $index => $item) {
                $list[$index] = $item['name'];
            }
        }

        $requiredAuthorities = env('AUTH_REQUIRED_AUTHORITIES');
        if (is_string($requiredAuthorities)) {
            $requiredAuthorities = [$requiredAuthorities];
        } elseif (!is_array($requiredAuthorities)) {
            $requiredAuthorities = [];
        }
        if (array_diff($requiredAuthorities, $list)) {
            throw new AccessDeniedException;
        }

        $userDetails->setAuthorities($list);

        Session::set('Auth::userInfo', $this->currentUser = $user);
        return $this->loginUserDetails($userDetails);
    }

    public function loginUserDetails(UserDetailsInterface $userDetails): UserDetailsInterface
    {
        Session::set('Auth::userDetails', $this->currentUserDetails = $userDetails);
        return $userDetails;
    }

    /**
     * @param string $email
     * @param string $password
     * @return UserDetailsInterface
     * @throws ServerException
     */
    public function register(string $email, string $password): UserDetailsInterface
    {
        if (!str_contains($email, '@')) {
            throw ValidationException::of('Invalid email format.');
        }

        if (User::find(['email' => $email])) {
            throw ValidationException::of('The email "%s" is in use.', $email);
        }

        $username = substr(explode('@', $email)[0], 0, 4)
            . substr(md5($email), 0, 8);

        $user = User::create([
            'email' => $email,
            'username' => $username,
            'password' => $this->encryptPassword($password),
            'status' => User::defaultStatus()
        ]);

        if (!$user) {
            throw ConflictException::of('Unable to register a user at the moment.');
        }

        $this->logout();
        return $this->loginUserDetails(UserDetails::fromUserEntity($user));
    }

    public function comparePassword(string $password, string $hash): bool
    {
        if (str_starts_with($hash, '{bcrypt}')) {
            $hash = substr($hash, 8);
        }

        return password_verify($password, $hash);
    }

    public function encryptPassword(string $password): string
    {
        return '{bcrypt}' . password_hash($password, PASSWORD_DEFAULT);
    }

    public function getUserPermissions(UserEntity $user): RepositoryInterface
    {
        return Role::repository()
            ->alias('R')
            ->withoutTrashed()
            ->innerJoin(UserRole::class . ' AS UR', 'UR.role_id', 'R.id')
            ->where('UR.' . UserRole::relation_secondary_column(), '=', $user->getId())
            ->select('R.*');
    }

    /**
     * @throws ServerException
     */
    public function createSession(UserEntity $user, string $remoteIP, int|float|string|\DateTimeInterface $expires): SessionEntity
    {
        $token = md5(serialize($data = [$user->getUsername(), $remoteIP, $expires]));

        return UserSession::create([
            'userId' => $user->getId(),
            'sessionId' => $token,
            'ip' => $remoteIP,
            'expiredAt' => $expires,
        ]);
    }

    /**
     * @throws ServerException
     * @throws DbException
     */
    public function updateSession(string $token, mixed $data): SessionEntity|false|null
    {
        $session = UserSession::find(['token' => $token]);

        if ($session === null) {
            return null;
        }

        if ($session->isExpired()) {
            return false;
        }

        return UserSession::update($session, ['data' => serialize($data)]);
    }

    /**
     * @throws DbException
     */
    public function deleteSession(string $token): ?bool
    {
        $session = UserSession::find(['token' => $token]);

        if ($session === null) {
            return null;
        }

        if ($session->isExpired()) {
            return false;
        }

        return UserSession::delete($session);
    }
}
