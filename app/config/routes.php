<?php

use App\Controllers\IndexController;
use Autumn\Extensions\Auth\Auth;
use Autumn\Extensions\Auth\Middlewares\LoginDeveloperMiddleware;
use Autumn\Extensions\Auth\Middlewares\LoginUserMiddleware;
use Autumn\Extensions\Cms\Cms;
use Autumn\Extensions\Shop\Shop;
use Autumn\System\Route;

include_once __DIR__ . '/routes-admin.php';
include_once __DIR__ . '/routes-catalog.php';

Route::guards('/user/**', LoginUserMiddleware::class);
Route::guards('/api/**', LoginDeveloperMiddleware::class);

Route::get('/(index(\.html)?)?', IndexController::class);
Route::get('/user(/index(\.html)?)?', \App\Controllers\User\IndexController::class);

Auth::mount();
Cms::mount();
Shop::mount();
