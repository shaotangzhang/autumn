<?php

namespace App\Controllers;

use App\Forms\Login\ForgetPasswordForm;
use App\Forms\Login\LoginForm;
use App\Forms\Login\RegisterForm;
use App\Services\LoginService;
use Autumn\System\Response;
use Autumn\System\View;

class LoginController extends AbstractController
{
    public const REDIRECT_DEFAULT = '/user';
    public const REDIRECT_LOGIN = '/login';

    private ?LoginService $loginService = null;

    /**
     * @return LoginService|null
     */
    public function getLoginService(): ?LoginService
    {
        return $this->loginService ??= LoginService::context();
    }

    // GET /login
    public function index(): View
    {
        return $this->view('login/index');
    }

    // POST /login
    public function login(LoginForm $form, LoginService $service): Response
    {
        return $this->respond($service->toSubmit($form), context: [
            'action' => __FUNCTION__,
            'redirect' => static::REDIRECT_LOGIN
        ]);
    }

    // GET /register
    public function create(): View
    {
        return $this->view('login/register');
    }

    // POST /register
    public function register(RegisterForm $form, LoginService $service): Response
    {
        return $this->respond($service->toSubmit($form), context: [
            'action' => __FUNCTION__,
            'redirect' => static::REDIRECT_LOGIN
        ]);
    }

    // GET /logout
    public function logout(LoginService $service): Response
    {
        $service->logout();
        return $this->redirect(static::REDIRECT_LOGIN);
    }

    // GET /forget-password
    public function forgetPassword(): View
    {
        return $this->view('login/forget-password');
    }

    // POST /forget-password
    public function resendPassword(ForgetPasswordForm $form, LoginService $service): Response
    {
        return $this->respond($service->toSubmit($form), context: [
            'action' => 'resend-password',
            'redirect' => static::REDIRECT_LOGIN
        ]);
    }

    // GET /reset-password
    public function recoverPassword(string $token, LoginService $service): View
    {
        $service->verifyRecoverToken($token);
        return $this->view('login/reset-password', compact('token'));
    }

    // POST /reset-password
    public function resetPassword(string $token, string $password, LoginService $service): Response
    {
        $service->resetPassword($token, $password);

        return $this->respond(LoginService::logout(...), context: [
            'action' => 'reset-password',
            'redirect' => static::REDIRECT_LOGIN
        ]);
    }
}