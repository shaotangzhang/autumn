<?php

namespace Autumn\Extensions\Cms;

use Autumn\System\Extension;

class Cms extends Extension
{
    public const REGISTERED_ENTITIES = [];

    public static function routes(string $appName = null, array $options = null): void
    {
        if ($fileName = static::realpath($appName ? "routes-$appName.php" : 'routes.php')) {
            include_once $fileName;
        }
    }
}