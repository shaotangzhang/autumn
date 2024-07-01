<?php

use Autumn\Extensions\Auth\Auth;
use Autumn\Extensions\Auth\Middlewares\LoginDeveloperMiddleware;
use Autumn\Extensions\Cms\Cms;
use Autumn\System\Route;

Route::guards('/api/**', LoginDeveloperMiddleware::class::class);

Route::group('/api', function () {

    Auth::mount('api');
    Cms::mount('api');
});