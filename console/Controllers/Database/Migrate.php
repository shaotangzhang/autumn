<?php
/**
 * Enflares PHP Framework
 *
 * Date:        2022/9/30
 */

namespace console\Controllers\Database;

use Autumn\App;
use Autumn\Database\Schema;
use Autumn\System\Controller;
use Autumn\System\Interfaces\Persistable;
use Throwable;

class Migrate extends Controller
{
    public static function main(string ...$args): void
    {
        if ($path = realpath(App::map('App', 'Database'))) {
            $classes = static::scanFolder($path);

            $manager = Schema::getEntityManager();

            foreach($classes as $name=>$file) {
                if(is_subclass_of($class = 'App\\Database\\' . $name, Persistable::class)) {
                    echo 'MIGRATE TABLE: ', $class, PHP_EOL;
                    if($manager->alertTable($class)) {
                        echo PHP_EOL, '=== SUCCESS ===';
                    }elseif($manager->createTable($class)){
                        $data = strtolower(strtr(strtr(substr($name, 0, -6), '/', '-'), '\\', '-'));
                        if($data = realpath(App::map('App', 'Database', 'seeding', $data.'.php'))) {
                            echo PHP_EOL, 'SEEDING from : ', $data, PHP_EOL;
                            try {
                                include $data;
                            }catch(Throwable $ex) {
                                debug_error($ex);
                            }
                        }
                        echo PHP_EOL, '=== SUCCESS ===';
                    }
                    echo PHP_EOL;
                }
            }
        }
    }

    public static function scanFolder(string $path, string $prefix = null): array
    {
        $list = [];

        foreach (glob($path . '/*Entity.php') as $file) {
            $class = $prefix . basename($file, '.php');
            $list[$class] = realpath($file);
        }

        foreach(glob($path . '/*', GLOB_ONLYDIR) as $folder) {
            $list += static::scanFolder($folder, $prefix . basename($folder) . '\\');
        }

        return $list;
    }
}