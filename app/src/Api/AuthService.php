<?php
/**
 * Autumn PHP Framework
 *
 * Date:        24/06/2024
 */

namespace App\Api;

use App\Models\User\User;
use App\Models\User\UserIp;
use App\Models\User\UserSession;
use Autumn\System\Service;

class AuthService extends Service
{
    public function login(string $username, string $password): ?User
    {
        $field = str_contains($username, '@') ? 'email' : 'username';

        $user = User::find([$field => $username, 'status' => 'active']);
        if (!$user || !password_verify($password, $user->getPassword())) {
            return null;
        }

        return $user;
    }

    public function getAcceptableIpList(int $id): array
    {
        $list = [];
        foreach (UserIp::find(['user_id' => $id]) as $item) {
            $list[] = $item['ip'];
        }
        return $list;
    }

    public function createSession(User $user, string $remoteIP, \DateTimeImmutable &$expires = null): string
    {
        $expires ??= new \DateTimeImmutable('+7 days');
        UserSession::create([
            'user_id' => $user->getId(),
            'session_id' => $token = md5(serialize([$user->getId(), $remoteIP, $expires])),
            'expires' => $expires
        ]);

        return $token;
    }
}