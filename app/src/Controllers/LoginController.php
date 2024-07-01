<?php

namespace App\Controllers;

use Autumn\Extensions\Auth\Models\Session\UserSession;

class LoginController extends AbstractController
{
    protected array $languageDomains = ['login'];

    public function login(string $username, string $password): UserSession
    {
        $userSession = $this->getAuthService()->authenticate($username, $password);
        $this->getAuthService()->login($userSession);
        return $userSession;
    }
}