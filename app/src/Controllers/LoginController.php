<?php
namespace App\Controllers;

use App\Api\AuthService;
use Autumn\Exceptions\ForbiddenException;
use Autumn\Exceptions\UnauthorizedException;
use Autumn\System\Controller;
use Autumn\System\Request;

class LoginController extends Controller
{
    protected array $languageDomains = ['login'];

    private ?AuthService $authService = null;

    /**
     * @return AuthService|null
     */
    public function getAuthService(): ?AuthService
    {
        return $this->authService ??= app(AuthService::class);
    }

    public function login(string $username, string $password): array
    {
        if (!$remoteIP = Request::realIP()) {
            throw ForbiddenException::of('Remote address is undetectable.');
        }

        $user = $this->getAuthService()->login($username, $password);
        if (!$user) {
            throw UnauthorizedException::of('Invalid username or password.');
        }

        $ipList = $this->getAuthService()->getAcceptableIpList($user->getId());

        if (!in_array($remoteIP, $ipList)) {
            throw ForbiddenException::of('Remote address `%s` is not acceptable.', $remoteIP);
        }

        $token = $this->authService->createSession($user, $remoteIP);

        return [
            'user' => $user,
            'token' => $token,
            'expires' => new \DateTimeImmutable('+7 days')
        ];
    }
}