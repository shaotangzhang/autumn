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
 * Class EntityDeletingEvent
 *
 * This class represents the event triggered before an entity is deleted.
 */
class EntityDeletingEvent extends StoppableEvent implements EntityEventInterface
{
    /**
     * @var Entity The entity instance being deleted.
     */
    private readonly Entity $entity;

    /**
     * EntityDeletingEvent constructor.
     *
     * @param Entity $entity The entity instance being deleted.
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