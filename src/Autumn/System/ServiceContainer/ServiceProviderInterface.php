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
    public static function mount(Application $application): void;
}