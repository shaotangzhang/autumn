<?php
/**
 * Autumn PHP Framework
 *
 * Date:        23/06/2024
 */

namespace App;

use Autumn\Database\Events\EntityEventDispatcher;

class Application extends \Autumn\System\Application
{
    public static function main(string ...$args): void
    {
    }

    protected function boot(): void
    {
        // register the EntityEventDispatcher as a handler of Event
        EntityEventDispatcher::register();
    }
}