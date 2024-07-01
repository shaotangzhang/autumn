<?php

namespace Autumn\Extensions\Auth\Controllers;

use Autumn\Extensions\Auth\Interfaces\CredentialInterface;
use Autumn\System\Responses\RedirectResponse;

class LoginController extends AbstractController
{
    public const METHOD_POST = 'login';

    protected string $viewPath = '/login/';

    protected array $languageDomains = ['login'];

    public function index(?string $redirect, ?string $error): mixed
    {
        $this->set('title', 'Login');

        return $this->view('index', [
            'error' => $error,
            'redirect' => $redirect ?: env('USER_LOGIN_DEFAULT_REDIRECT', '/')
        ]);
    }

    public function login(string $username, string $password, string $redirect = null, bool $rememberMe = null): RedirectResponse|CredentialInterface
    {
        $authService = $this->getAuthService();

        $userSession = $authService->authenticate($username, $password);
        $credential = $authService->login($userSession);

        if ($redirect) {
            return $this->redirect($redirect);
        }

        return $credential;
    }
}
