<?php
/**
 * Autumn PHP Framework
 *
 * Date:        25/06/2024
 */

namespace Autumn\Database\Events;

use Autumn\Database\Models\Entity;
use Autumn\Events\StoppableEvent;

/**
 * Class EntityCreatingEvent
 *
 * This class represents the event triggered before an entity is created.
 */
class EntityCreatingEvent extends StoppableEvent implements EntityEventInterface
{
    /**
     * @var Entity The entity instance being created.
     */
    private readonly Entity $entity;

    /**
     * EntityCreatingEvent constructor.
     *
     * @param Entity $entity The entity instance being created.
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