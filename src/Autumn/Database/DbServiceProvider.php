<?php
/**
 * Autumn PHP Framework
 *
 * Date:        26/06/2024
 */

namespace Autumn\Database;

use Autumn\Database\Events\EntityEventDispatcher;
use Autumn\System\Application;
use Autumn\System\ServiceContainer\ServiceContainerInterface;
use Autumn\System\ServiceContainer\ServiceProviderInterface;

class DbServiceProvider implements ServiceProviderInterface
{
    public static function register(ServiceContainerInterface $container): void
    {
    }

    public static function boot(Application $application): void
    {
        EntityEventDispatcher::register();
    }
}