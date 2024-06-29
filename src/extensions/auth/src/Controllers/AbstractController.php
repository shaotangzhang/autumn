<?php
namespace Autumn\Extensions\Auth\Controllers;

use Autumn\Extensions\Auth\Services\AuthService;
use Autumn\System\Controller;

class AbstractController extends Controller
{
    private ?AuthService $authService = null;

    /**
     * @return AuthService|null
     */
    public function getAuthService(): ?AuthService
    {
        return $this->authService ??= app(AuthService::class);
    }
}