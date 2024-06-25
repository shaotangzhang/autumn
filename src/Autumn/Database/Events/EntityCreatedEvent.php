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
 * Class EntityCreatedEvent
 *
 * This class represents the event triggered after an entity is created.
 */
class EntityCreatedEvent implements EntityEventInterface
{
    /**
     * @var Entity The entity instance that has been created.
     */
    private readonly Entity $entity;

    /**
     * EntityCreatedEvent constructor.
     *
     * @param Entity $entity The entity instance that has been created.
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
