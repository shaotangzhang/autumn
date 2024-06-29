<?php
namespace Autumn\Extensions\Auth\Controllers;

class LoginController extends AbstractController
{
    protected string $viewPath = '/login/';

    public function index(?string $redirect, ?string $error): mixed
    {
        $this->set('title', 'Login');
        return $this->view('index', ['error' => $error, 'redirect' => $redirect]);
    }

    public function post(string $username, string $password, ?string $redirect): mixed
    {
        $this->getAuthService()->login($username, $password);
        return $this->redirect($redirect ?: env('AUTH_LOGIN_DEFAULT_REDIRECT') ?: '/');
    }
}
