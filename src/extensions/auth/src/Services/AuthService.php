<?php

namespace Autumn\Extensions\Auth\Services;

use Autumn\Database\Db;
use Autumn\Exceptions\AuthenticationException;
use Autumn\Exceptions\SystemException;
use Autumn\Extensions\Auth\Interfaces\CredentialInterface;
use Autumn\Extensions\Auth\Models\Credentials\DefaultCredential;
use Autumn\Extensions\Auth\Models\Role\Role;
use Autumn\Extensions\Auth\Models\Session\UserSession;
use Autumn\Extensions\Auth\Models\User\User;
use Autumn\Extensions\Auth\Models\User\UserDetails;
use Autumn\Extensions\Auth\Models\User\UserEntity;
use Autumn\Extensions\Auth\Models\User\UserRole;
use Autumn\System\Request;
use Autumn\System\Session;
use Psr\SimpleCache\InvalidArgumentException;

class AuthService extends AbstractService
{
    public const SESSION_DEFAULT_LIFETIME_IN_SECONDS = 86400 * 7;   // 7 Days
    public const USER_SESSION_KEY = 'AUTH_USER_SESSION';

    private false|UserSession|null $userSession = null;

    public static function session(): ?UserSession
    {
        try {
            $session = Session::get(static::USER_SESSION_KEY);
            if ($session instanceof UserSession) {
                return $session;
            }
        } catch (InvalidArgumentException) {
        }

        return null;
    }

    /**
     * Attempt to authenticate a user based on credentials.
     *
     * @param string $username
     * @param string $password
     * @return UserSession
     */
    public function authenticate(string $username, string $password): UserSession
    {
        if ($this->isEmail($username)) {
            $user = $this->loadUserByEmail($username);
        } else {
            $user = $this->loadUserByUsername($username);
        }

        if (!$user || !$this->verifyUserPassword($password, $user)) {
            throw AuthenticationException::of('Invalid username or password.');
        }

        $session = $this->createSession($user);
        try {
            Session::set(static::USER_SESSION_KEY, $session);
        } catch (InvalidArgumentException $ex) {
            throw SystemException::of('Failed to store the user session.', $ex);
        }
        return $session;
    }

    public function createSession(UserEntity $user): UserSession
    {
        $userDetails = UserDetails::fromUserEntity($user)->verify();

        $userId = $user->getId();

        $session = new UserSession;
        $session->setUserId($userId);
        $session->setProfile($user);
        $session->setIp(Request::realIPv4() ?? '127.0.0.1');
        $session->setUserDetails($userDetails);
        $session->setPreferences($this->loadUserPreferences($userId));

        $query = $this->loadUserRoles($userId);

        $roles = [];
        // if ($userId === 1) {
        //     $authorities = [Role::SUPERVISOR];
        //     $roles = [Role::supervisor()];
        // } else {
            $authorities = $userDetails->getAuthorities();
            foreach ($query as $role) {
                $roles[] = $role;
                $authorities[] = $role->getName();
            }
        // }
        $userDetails->setAuthorities($authorities);
        $session->setRoles($roles);

        $token = $this->createAuthToken($session);
        $session->setSessionExpiration($token['expires']);
        $session->setAuthToken($token['token']);
        $session->setSessionId(md5(serialize($token)));

        return $session;
    }

    public function createAuthToken(UserSession $session): array
    {
        $expires = time() + $this->getSessionLifetime();
        try {
            $randomBytes = random_bytes(16); // 16 bytes will generate a 32-character hex string
            $token = bin2hex($randomBytes);
        } catch (\Exception) {
            $token = md5(serialize($session) . microtime());
        }
        return compact('token', 'expires');
    }

    /**
     * @param string $token
     * @return UserSession|null
     * @throws \Throwable
     */
    public function refreshAuthToken(string $token): ?UserSession
    {
        return Db::transaction(function () use ($token) {
            // Load the session based on the provided token
            $session = $this->loadSessionByToken($token);
            if (!$session) {
                return null; // Return null if session with the token is not found
            }

            // Create a new authentication token and update session details
            $newToken = $this->createAuthToken($session);
            $session->setSessionExpiration($newToken['expires']);
            $session->setAuthToken($newToken['token']);

            // Update the session in the database
            UserSession::update($session, [
                'sid' => $session->getAuthToken(),
                'deleted_at' => $session->getSessionExpiration()
            ]);

            return $session; // Return the updated session
        }, UserSession::class); // Specify the connection for the transaction
    }

    public function loadSessionByToken(string $token): ?UserSession
    {
        return UserSession::findBy(['sid' => $token])->withoutTrashed()->first();
    }

    public function getSessionLifetime(): int
    {
        // Retrieve lifetime from environment variable AUTH_SESSION_LIFETIME
        $lifetime = filter_var(env('AUTH_SESSION_LIFETIME'), FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

        // Check if the retrieved lifetime is valid and greater than zero
        if ($lifetime > 0) {
            return $lifetime;
        }

        // If lifetime is not valid or not provided, return default lifetime
        return static::SESSION_DEFAULT_LIFETIME_IN_SECONDS;
    }

    public function isEmail(string $text): bool
    {
        return str_contains($text, '@');
    }

    public function verifyUserPassword(string $password, string|UserEntity $compare): bool
    {
        if (!is_string($compare)) {
            $compare = $compare->getPassword();
        }

        return password_verify($password, $compare);
    }

    public function loadUserByEmail(string $email): ?User
    {
        return User::findBy(['email' => $email])->withoutTrashed()->first();
    }

    public function loadUserByUsername(string $username): ?User
    {
        return User::findBy(['username' => $username])->withoutTrashed()->first();
    }

    /**
     * @param int $userId
     * @return Role[]
     */
    public function loadUserRoles(int $userId): iterable
    {
        return Role::repository()
            ->alias('PRI')
            ->withoutTrashed()
            ->innerJoin(UserRole::class . ' AS R', 'PRI.id', 'R.role_id')
            ->and('R.user_id', $userId);
    }

    public function loadUserPreferences(int $userId): array
    {
        // implements later
        return [];
    }

    /**
     * @return UserSession|null
     */
    public function getUserSession(): ?UserSession
    {
        return ($this->userSession ??= (static::session() ?? false)) ?: null;
    }

    /**
     * Check if a user is currently authenticated.
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->getUserSession() !== null;
    }

    public function login(UserSession $session): CredentialInterface
    {
        $this->userSession = $session;
        try {
            Session::set(static::USER_SESSION_KEY, $session);
            return $this->createCredential($session);
        } catch (InvalidArgumentException $ex) {
            throw SystemException::of('Failed to store the user session.', $ex);
        }
    }

    /**
     * Logout the current user.
     */
    public function logout(): void
    {
        try {
            $this->getUserSession();
            $this->userSession = null;
            Session::delete(static::USER_SESSION_KEY);
            Session::close();
        } catch (InvalidArgumentException $ex) {
            throw SystemException::of('Failed to destroy the user session.', $ex);
        }
    }

    public function createCredential(UserSession $session): CredentialInterface
    {
        return DefaultCredential::fromUserSession($session);
    }
}
