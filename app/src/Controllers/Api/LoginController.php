<?php
/**
 * Autumn PHP Framework
 *
 * Date:        24/06/2024
 */

namespace App\Controllers\Api;

use App\Api\AuthService;
use Autumn\Exceptions\ForbiddenException;
use Autumn\Exceptions\UnauthorizedException;
use Autumn\System\Controller;
use Autumn\System\Request;
use Autumn\System\Response;
use Autumn\System\Responses\JsonResponse;

class LoginController extends Controller
{
    protected array $languageDomains = ['login'];

    private ?AuthService $authService = null;

    /**
     * @return AuthService|null
     */
    public function getAuthService(): ?AuthService
    {
        return $this->authService ??= make(AuthService::class);
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

        $token = $this->authService->createSession($user, $remoteIP, $expires);

        return [
            'user' => $user,
            'token' => $token,
            'expires' => $expires->format('c')
        ];
    }
}