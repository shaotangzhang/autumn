<?php

use App\Controllers\Shop\CartController;
use App\Controllers\Shop\CategoryController;
use App\Controllers\Shop\CheckoutController;
use App\Controllers\Shop\ProductController;
use App\Controllers\Shop\SearchController;
use App\Controllers\User\NotificationController;
use App\Controllers\User\OrderController;
use App\Controllers\User\PaymentController;
use App\Controllers\User\ProfileController;
use Autumn\System\Route;

// 产品展示和详情 ProductController
Route::get('/products', ProductController::class);  // 默认调用index方法，显示所有产品
Route::get('/products/{id}', ProductController::class . '@show');  // 显示单个产品详情

// 分类展示 CategoryController
Route::get('/categories', CategoryController::class);  // 默认调用index方法，显示所有分类
Route::get('/categories/{id}', CategoryController::class . '@show');  // 显示单个分类及其产品

// 搜索功能 SearchController
Route::get('/search', SearchController::class);  // 默认调用index方法，执行搜索

// 购物车管理 CartController
Route::get('/cart', CartController::class);  // 默认调用index方法，查看购物车
Route::post('/cart/add', CartController::class . '@addToCart');  // 添加到购物车
Route::post('/cart/update/{cartItemId}', CartController::class . '@updateCartItem');  // 更新购物车项
Route::post('/cart/remove/{cartItemId}', CartController::class . '@removeFromCart');  // 从购物车中移除
Route::post('/cart/save', CartController::class . '@saveCart');  // 保存购物车

// 结帐操作 CheckoutController
Route::get('/cart/checkout', CheckoutController::class);  // 默认调用index方法，查看结帐页面
Route::post('/cart/checkout', CheckoutController::class);  // 默认调用post方法，执行结帐步骤

// 订单和交易管理 OrderController
Route::get('/user/orders', OrderController::class);  // 默认调用index方法，查看订单历史
Route::get('/user/orders/{orderId}', OrderController::class . '@show');  // 查看订单详情

// 个人信息管理 ProfileController
Route::get('/user/profile', ProfileController::class);  // 默认调用index方法，查看个人资料
Route::post('/user/profile', ProfileController::class . '@update');  // 更新个人资料

// 支付管理 PaymentController
Route::get('/user/payments', PaymentController::class);  // 默认调用index方法，查看支付方式
Route::post('/user/payments/process', PaymentController::class . '@processPayment');  // 处理支付
Route::get('/user/payments/status/{orderId}', PaymentController::class . '@getStatus');  // 查看支付状态

// 通知和消息 NotificationController
Route::get('/user/notifications', NotificationController::class);  // 默认调用index方法，查看通知
Route::post('/user/notifications/read/{notificationId}', NotificationController::class . '@markAsRead');  // 标记通知为已读


