<?php

use Autumn\System\Route;
use Console\Controllers\Crawler\ArgiCnController;
use Console\Controllers\Crawler\GengZhongBangController;
use Console\Controllers\Crawler\My478Controller;
use Console\Controllers\Crawler\W3schoolController;
use Console\Controllers\Crawler\ZhiWuZiLiaoController;
use Console\Controllers\IndexController;
use Console\Controllers\MigrationController;
use Console\Controllers\ShopifyController;

Route::get('/', IndexController::class);
Route::get('/migrate', MigrationController::class);
Route::get('/shopify', ShopifyController::class);
Route::get('/gengzhongbang', GengZhongBangController::class);
Route::get('/my478', My478Controller::class);
Route::get('/zhiwuziliao', ZhiWuZiLiaoController::class);
Route::get('/argicn', ArgiCnController::class);
Route::get('/w3school', W3schoolController::class);