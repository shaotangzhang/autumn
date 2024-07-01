<?php

use App\Admin\IndexController;
use Autumn\Extensions\Auth\Auth;
use Autumn\Extensions\Auth\Middlewares\LoginAdminMiddleware;
use Autumn\Extensions\Cms\Cms;
use Autumn\System\Route;

Route::guards('/admin/**', LoginAdminMiddleware::class);

Route::group('/admin', function () {

    Route::get('(/index(\.html)?)?', IndexController::class);

    Auth::mount('admin');
    Cms::mount('admin');
});