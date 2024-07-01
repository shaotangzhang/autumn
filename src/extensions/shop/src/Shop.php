<?php

namespace Autumn\Extensions\Shop;

use Autumn\Extensions\Cms\Cms;
use Autumn\System\Extension;

class Shop extends Extension
{
    /**
     * The version of the Shop extension.
     */
    public const VERSION = "1.0.0";

    public const REQUIRED_EXTENSIONS = [
        Cms::class
    ];


    public const REGISTERED_ENTITIES = [
    ];
}