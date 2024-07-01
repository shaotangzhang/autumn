<?php

namespace Autumn\Extensions\Auth\Controllers;

use Autumn\System\Responses\RedirectResponse;

class LogoutController extends AbstractController
{
    public const METHOD_POST = 'logout';

    protected string $viewPath = 'login/';

    public function index(string $redirect = null): RedirectResponse
    {
        $this->getAuthService()->logout();

        return $this->redirect($redirect ?: '/login');
    }

    public function logout(): array
    {
        $this->getAuthService()->logout();
        return $this->actionResult(__FUNCTION__, true);
    }
}