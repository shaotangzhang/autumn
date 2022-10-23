<?php
/**
 * Enflares PHP Framework
 *
 * Date:        2022/9/30
 */

namespace console\Controllers\Database;

use Autumn\App;
use Autumn\System\Controller;
use Throwable;

class Seed extends Controller
{
    public static function main(string $name): void
    {
        if($file = realpath(App::map('App', 'Database', 'seeding', $name.'.php'))) {
            echo PHP_EOL, 'SEEDING from : ', $file, PHP_EOL;
            try {
                include $file;
            }catch(Throwable $ex) {
                debug_error($ex);
            }
            echo PHP_EOL, '=== SUCCESS ===';
        }
    }
}