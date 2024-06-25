<?php
/**
 * Autumn PHP Framework
 *
 * Date:        25/06/2024
 */

namespace Autumn\Database\Events;

use Autumn\Database\Models\Entity;
use Autumn\Events\EventInterface;

interface EntityEventInterface extends EventInterface
{
    public function getEntity(): Entity;
}