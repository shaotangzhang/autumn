<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/05/2024
 */

namespace Autumn\Extensions\Cms\Models\Traits;

use Autumn\Attributes\Transient;
use Autumn\Exceptions\ValidationException;

trait ItemsTrait
{
    #[Transient]
    private array $items = [];

    /**
     * Get the items collection.
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Set the items collection.
     *
     * @param iterable|null $items
     */
    public function setItems(iterable $items = null): void
    {
        foreach ($items ?? [] as $item) {
            $this->appendItem($item);
        }
    }

    /**
     * Append an item to the collection.
     *
     * @param self|array $entity The entity or array representation of the entity to append.
     * @throws ValidationException If the provided entity is not of the correct type.
     */
    public function appendItem(self|array $entity): void
    {
        if (is_array($entity)) {
            $entity = static::from($entity);
        }

        if ($entity instanceof static) {
            $this->items[] = $entity;
        } else {
            throw ValidationException::of('Invalid data type to append as an item.');
        }
    }

    /**
     * Check if the collection has items.
     *
     * @return int
     */
    public function hasItems(): int
    {
        return count($this->items) > 0;
    }

    /**
     * Check if the collection has a specific item.
     *
     * @param int|self $entity The ID of the entity or the entity itself.
     * @return bool
     */
    public function hasItem(int|self $entity): bool
    {
        if (is_int($entity)) {
            foreach ($this->items as $item) {
                if ($item->getId() === $entity) {
                    return true;
                }
            }
            return false;
        }

        return in_array($entity, $this->items, true);
    }

    /**
     * Clear the item's collection.
     */
    public function clearItems(): void
    {
        $this->items = [];
    }
}
