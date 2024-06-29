<?php
namespace App\Api;

use App\Models\Developer\Developer;
use App\Models\Developer\DeveloperIp;
use App\Models\Developer\DeveloperSession;
use Autumn\System\Service;

class AuthService extends Service
{
    public function login(string $username, string $password): ?Developer
    {
        $field = str_contains($username, '@') ? 'email' : 'username';

        $user = Developer::find([$field => $username, 'status' => 'active']);
        if (!$user || !password_verify($password, $user->getPassword())) {
            return null;
        }

        return $user;
    }

    public function getAcceptableIpList(int $id): array
    {
        $list = [];
        foreach (DeveloperIp::find(['user_id' => $id]) as $item) {
            $list[] = $item['ip'];
        }
        return $list;
    }

    public function createSession(Developer $user, string $remoteIP, \DateTimeImmutable &$expires = null): string
    {
        $expires ??= new \DateTimeImmutable('+7 days');
        DeveloperSession::create([
            'user_id' => $user->getId(),
            'sid' => $token = md5(serialize([$user->getId(), $remoteIP, $expires])),
            'expired_at' => $expires
        ]);

        return $token;
    }
}