<?php
/**
 * Autumn PHP Framework
 *
 * Date:        23/06/2024
 */

use App\Controllers\IndexController;
use Autumn\Extensions\Cms\Cms;
use Autumn\System\Route;

Route::get('/(index(\.html)?)?', IndexController::class);

Cms::routes('/cms');