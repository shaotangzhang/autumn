<?php
/**
 * Autumn PHP Framework
 *
 * Date:        16/06/2024
 */

namespace Autumn\System\ServiceContainer;

use Autumn\System\Application;

interface ServiceProviderInterface
{
    public static function register(ServiceContainerInterface $container): void;

    public static function boot(Application $application): void;
}