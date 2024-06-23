<?php
/**
 * Autumn PHP Framework
 *
 * Date:        16/06/2024
 */

namespace Autumn\System\ServiceContainer;
interface ServiceProviderInterface
{
    public function register(ServiceContainerInterface $container): void;
}