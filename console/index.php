<?php

defined('SRC_ROOT') and die();
defined('PUB_ROOT') or define('PUB_ROOT', __DIR__);
defined('DOC_ROOT') or define('DOC_ROOT', dirname(__DIR__));

require_once DOC_ROOT . '/src/autumn/boot.php';

app('console')->send();