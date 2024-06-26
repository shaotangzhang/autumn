<?php
/**
 * Autumn PHP Framework
 *
 * Date:        23/06/2024
 */

namespace Autumn\Extensions\Cms;

use Autumn\Extensions\Auth\Auth;
use Autumn\System\Application;
use Autumn\System\Extension;

class Cms extends Extension
{
    /**
     * The version of the CMS extension.
     */
    public const VERSION = "1.0.0";

    public const REQUIRED_EXTENSIONS = [
        Auth::class
    ];

    /**
     * List of required middlewares that should be applied to the application.
     * Example: ['auth', 'csrf']
     */
    public const REQUIRED_MIDDLEWARES = ['auth', 'csrf'];

    public static function boot(Application $application): void
    {
        parent::boot($application);
    }
}