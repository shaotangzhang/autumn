<?php
/**
 * Autumn PHP Framework
 *
 * Date:        24/05/2024
 */

use App\Models\Developer\Developer;
use App\Models\Developer\DeveloperIp;
use App\Models\Developer\DeveloperSession;
use Autumn\Database\Migration\Migration;
use Autumn\Extensions\Auth\Auth;
use Autumn\Extensions\Cms\Cms;

Migration::context()->registerExtension(
    Auth::class,
// Cms::class,
);

Migration::context()->registerEntity(
    Developer::class,
    DeveloperIp::class,
    DeveloperSession::class
);