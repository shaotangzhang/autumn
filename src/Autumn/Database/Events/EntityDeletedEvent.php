<?php
/**
 * Autumn PHP Framework
 *
 * Date:        25/06/2024
 */

namespace Autumn\Database\Events;

use Autumn\Database\Models\Entity;
use Autumn\Events\EventInterface;

/**
 * Class EntityDeletedEvent
 *
 * This class represents the event triggered after an entity is deleted.
 */
class EntityDeletedEvent implements EntityEventInterface
{
    /**
     * @var Entity The entity instance that has been deleted.
     */
    private readonly Entity $entity;

    /**
     * EntityDeletedEvent constructor.
     *
     * @param Entity $entity The entity instance that has been deleted.
     */
    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
    }

    public function getEntity(): Entity
    {
        return $this->entity;
    }
}
