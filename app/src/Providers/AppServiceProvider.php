<?php

namespace App\Providers;

use App\Api\AuthService;
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
        $container->bind(\Autumn\Extensions\Auth\Services\AuthService::class, AuthService::class);
    }

    public static function boot(Application $application): void
    {
        $application->applyMiddleware(
            // LoginMiddleware::class
        );
    }
}