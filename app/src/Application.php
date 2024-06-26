<?php
/**
 * Autumn PHP Framework
 *
 * Date:        23/06/2024
 */

namespace App;

use App\Providers\AppServiceProvider;
use Autumn\Database\DbServiceProvider;

class Application extends \Autumn\System\Application
{
    protected array $serviceProviders = [
        DbServiceProvider::class,   // This can be a must if ORM is used
        AppServiceProvider::class,
    ];
}