<?php

use Autumn\Extensions\Auth\Controllers\LoginController;
use Autumn\Extensions\Auth\Controllers\LogoutController;
use Autumn\Extensions\Auth\Controllers\RegisterController;
use Autumn\System\Route;

Route::root(function () {
    Route::get('/login', LoginController::class);
    Route::post('/login', LoginController::class);
    Route::get('/logout', LogoutController::class);
    Route::post('/logout', LogoutController::class);
    Route::get('/register', RegisterController::class);
    Route::post('/register', RegisterController::class);
});