<?php

use Autumn\Extensions\Auth\Controllers\LoginController;
use Autumn\System\Route;

Route::root(function () {
    Route::get('/login', LoginController::class);
    Route::post('/login', LoginController::class);
});