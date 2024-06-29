<?php

use Autumn\System\Route;
use Console\Controllers\IndexController;
use Console\Controllers\MigrationController;

Route::get('/', IndexController::class);
Route::get('/migrate', MigrationController::class);
