<?php

use App\Controllers\IndexController;
use Autumn\Extensions\Cms\Cms;
use Autumn\System\Route;

Route::get('/(index)?', IndexController::class);

Cms::routes('app', []);