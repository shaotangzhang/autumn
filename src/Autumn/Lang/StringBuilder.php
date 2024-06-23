<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/05/2024
 */

namespace Autumn\Lang;

use Autumn\Interfaces\ArrayInterface;

class StringBuilder implements \Stringable, ArrayInterface, \ArrayAccess, \IteratorAggregate
{
    private array $items = [];
    private string $joiner = '';

    public function __construct(string $joiner = null)
    {
        $this->joiner = $joiner ?? '';
    }

    /**
     * Create a StringBuilder instance from an array.
     *
     * @param iterable $data The input list.
     * @param string|\Closure|null $joiner The joiner string or a closure that returns a joiner string.
     * @return static The created StringBuilder instance.
     */
    public static function of(iterable $data, string|\Closure $joiner = null): static
    {
        if ($joiner instanceof \Closure) {
            $instance = new static($joiner());
        } else {
            $instance = new static($joiner);
        }

        foreach ($data as $item) {
            if (is_iterable($item)) {
                $item = static::of($item, $joiner);
            }

            $instance->add($item);
        }

        return $instance;
    }

    public function __toString(): string
    {
        return implode($this->joiner, $this->items);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * Add a string to the builder.
     *
     * @param bool|int|float|string|\Stringable|null $item
     * @return $this
     */
    public function add(bool|int|float|string|\Stringable|null $item): static
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * Append strings to the builder.
     *
     * @param string ...$items
     * @return $this
     */
    public function append(bool|int|float|string|\Stringable|null ...$items): static
    {
        array_push($this->items, ...$items);
        return $this;
    }

    public function prepend(bool|int|float|string|\Stringable|null ...$items): static
    {
        array_unshift($this->items, ...$items);
        return $this;
    }

    /**
     * Remove a string from the builder by value.
     *
     * @param bool|int|float|string|\Stringable|null $item
     * @return $this
     */
    public function remove(bool|int|float|string|\Stringable|null $item): static
    {
        $index = array_search($item, $this->items);
        if ($index !== false) {
            unset($this->items[$index]);
        }
        $this->items = array_values($this->items); // Reindex array
        return $this;
    }

    /**
     * Clear all strings from the builder.
     *
     * @return $this
     */
    public function clear(): static
    {
        $this->items = [];
        return $this;
    }

    /**
     * Get the string at a specific index.
     *
     * @param int $index
     * @return string|null
     */
    public function get(int $index): ?string
    {
        return $this->items[$index] ?? null;
    }

    /**
     * Set the string at a specific index.
     *
     * @param int $index
     * @param bool|int|float|string|\Stringable|null $item
     * @return $this
     */
    public function set(int $index, bool|int|float|string|\Stringable|null $item): static
    {
        $this->items[$index] = $item;
        return $this;
    }

    /**
     * Check if the builder contains a specific string.
     *
     * @param bool|int|float|string|\Stringable|null $item
     * @return bool
     */
    public function contains(bool|int|float|string|\Stringable|null $item): bool
    {
        return in_array($item, $this->items, true);
    }

    // Implement ArrayInterface methods
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
        $this->items = array_values($this->items); // Reindex array
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }
}
