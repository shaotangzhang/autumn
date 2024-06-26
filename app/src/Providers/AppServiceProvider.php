<?php
/**
 * Autumn PHP Framework
 *
 * Date:        26/06/2024
 */

namespace App\Providers;

use Autumn\Logging\ConsoleLogger;
use Autumn\System\Application;
use Autumn\System\ServiceContainer\ServiceContainerInterface;
use Autumn\System\ServiceContainer\ServiceProviderInterface;
use Psr\Log\LoggerInterface;

class AppServiceProvider implements ServiceProviderInterface
{
    public static function register(ServiceContainerInterface $container): void
    {
        $container->bind(LoggerInterface::class, ConsoleLogger::class);

    }

    public static function boot(Application $application): void
    {
    }
}