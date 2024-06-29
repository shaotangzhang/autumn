<?php
/**
 * Autumn PHP Framework
 *
 * Date:        9/05/2024
 */

namespace Autumn\Extensions\Auth\Controllers;

class LogoutController extends AbstractController
{
    protected string $viewPath = 'login/';

    public function index(?string $redirect): mixed
    {
        $this->set('title', 'Logout');
        return $this->view('logout', ['redirect' => $redirect]);
    }

    public function post(?string $redirect): mixed
    {
        $this->getAuthService()->logout();

        return $this->redirect($redirect ?: '/');
    }
}