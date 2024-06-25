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
 * Class EntityUpdatedEvent
 *
 * This class represents the event triggered after an entity is updated.
 */
class EntityUpdatedEvent implements EntityEventInterface
{
    /**
     * @var Entity The entity instance that has been updated.
     */
    private readonly Entity $entity;

    /**
     * EntityUpdatedEvent constructor.
     *
     * @param Entity $entity The entity instance that has been updated.
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
