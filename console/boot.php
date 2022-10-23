<?php
declare(strict_types=1);

use Autumn\App;
use console\Application;

if (version_compare(PHP_VERSION, '8.1.0') < 0) {
    exit('PHP version must be over 8.1.0');
}

if (defined('PUB_ROOT')) return;
defined('PHP_TEST') or define('PHP_TEST', false);
defined('PUB_ROOT') or define('PUB_ROOT', __DIR__);
defined('DOC_ROOT') or define('DOC_ROOT', dirname(__DIR__));
defined('CONTROLLER_PREFIX') or define('CONTROLLER_PREFIX', 'console\\Controllers');
defined('CONTROLLER_SUFFIX') or define('CONTROLLER_SUFFIX', '');

require(DOC_ROOT . '/vendor/autoload.php');

$_SERVER['PATH_INFO'] = '/' . ($_SERVER['argv'][1] ?? '');
$_SERVER['REQUEST_METHOD'] = 'GET';

App::run(Application::class)->end();