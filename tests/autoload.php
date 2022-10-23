<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/9/27
 */


defined('DOC_ROOT') or define('DOC_ROOT', dirname(__DIR__));
defined('PHP_TEST') or define('PHP_TEST', true);

require(DOC_ROOT . '/vendor/autoload.php');

ob_start();

$_SERVER['PATH_INFO'] = '/';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';