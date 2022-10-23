<?php
/**
 * Enflares PHP Framework
 *
 * Date:        2022/9/30
 */

use App\Models\Auth\User;

$user = new User();
$user->setUsername($_ENV['ADMIN_USERNAME'] ??'admin');
$user->setPassword($_ENV['ADMIN_PASSWORD'] ?? $_ENV['DB_PASS'] ?? '123456');
$user->setEmail($_ENV['ADMIN_EMAIL'] ?? 'admin@website.com');
$user->setNickname('Admin');
$user->setType('supervisor');
$user->setStatus('active');
$user->setIp('127.0.0.1');
$user->save();