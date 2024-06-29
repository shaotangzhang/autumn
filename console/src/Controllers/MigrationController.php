<?php
/**
 * Autumn PHP Framework
 *
 * Date:        24/05/2024
 */

namespace Console\Controllers;

use Autumn\App;
use Autumn\Database\Migration\Migration;
use Autumn\System\Controller;

class MigrationController extends Controller
{
    /**
     * @return string
     */
    public function index(): string
    {
        if (realpath($file = App::path('migration', 'index.php'))) {
            include_once $file;
        }

        Migration::context()->migrate();
        return "Migrations executed successfully.\n";
    }
}