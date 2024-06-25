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
 * Class EntityUpdatingEvent
 *
 * This class represents the event triggered before an entity is updated.
 */
class EntityUpdatingEvent extends StoppableEvent implements EntityEventInterface
{
    /**
     * @var Entity The entity instance being updated.
     */
    private readonly Entity $entity;

    /**
     * @var array|null The changes being applied to the entity.
     */
    private ?array $changes;

    /**
     * EntityUpdatingEvent constructor.
     *
     * @param Entity $entity The entity instance being updated.
     * @param array|null $changes The changes being applied to the entity.
     */
    public function __construct(Entity $entity, ?array $changes = null)
    {
        $this->entity = $entity;
        $this->changes = $changes;
    }

    public function getEntity(): Entity
    {
        return $this->entity;
    }

    /**
     * @return array|null
     */
    public function getChanges(): ?array
    {
        return $this->changes;
    }
}