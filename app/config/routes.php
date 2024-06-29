<?php

use App\Controllers\IndexController;
use Autumn\Extensions\Auth\Auth;
use Autumn\Extensions\Auth\Middlewares\LoginDeveloperMiddleware;
use Autumn\Extensions\Auth\Middlewares\LoginUserMiddleware;
use Autumn\Extensions\Cms\Cms;
use Autumn\System\Route;

include_once __DIR__ . '/routes-admin.php';

Route::guards('/user/**', LoginUserMiddleware::class);
Route::guards('/api/**', LoginDeveloperMiddleware::class);

Route::get('/(index(\.html)?)?', IndexController::class);

Auth::mount();
Cms::mount();
