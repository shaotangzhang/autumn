<?php

use Autumn\App;

defined('SRC_ROOT') and die();
defined('PUB_ROOT') or define('PUB_ROOT', __DIR__);
defined('DOC_ROOT') or define('DOC_ROOT', dirname(__DIR__));

$classLoader = require DOC_ROOT . '/vendor/autoload.php';
App::boot('application-name', $classLoader)->send();