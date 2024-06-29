<?php
namespace Autumn\Extensions\Auth\Controllers;

class RegisterController extends AbstractController
{
    protected string $viewPath = 'login/';

    public function index(?string $redirect, ?string $error): mixed
    {
        $this->set('title', 'Register');
        return $this->view('register', ['error' => $error, 'redirect' => $redirect]);
    }

    public function post(string $email, string $password, ?string $redirect): mixed
    {
        $this->getAuthService()->register($email, $password);

        return $this->redirect($redirect ?: '/users', 302, 'Register is successful.');
    }
}
