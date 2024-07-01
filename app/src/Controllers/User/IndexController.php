<?php

namespace App\Controllers\User;

class IndexController extends AbstractController
{
    public function index(): mixed
    {
        return $this->getAuthService()->getUserSession();
    }
}