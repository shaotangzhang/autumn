<?php
/**
 * Autumn PHP Framework
 *
 * Date:        23/06/2024
 */

namespace App;

use App\Models\User\User;
use Autumn\Database\Events\EntityCreatedEvent;
use Autumn\Database\Events\EntityEventDispatcher;
use Autumn\Database\Events\EntityEventHandlerInterface;
use Autumn\Database\Events\EntityEventInterface;

class Application extends \Autumn\System\Application
{
    public static function main(string ...$args): void
    {
    }

    protected function boot(): void
    {
        EntityEventDispatcher::hook(User::class, new class implements EntityEventHandlerInterface {
            public function creating(EntityEventInterface $event): void
            {
                echo 'creating ' . $event->getEntity()::entity_name();
            }
        });

        User::hook(new class implements EntityEventHandlerInterface {
            public function creating(EntityEventInterface $event): void
            {
                echo 'creating ' . $event->getEntity()::entity_name();
            }
        });
    }
}