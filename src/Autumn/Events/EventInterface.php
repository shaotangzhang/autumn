<?php

namespace Autumn\Events;

/**
 * Interface for defining event objects.
 */
interface EventInterface
{
    /**
     * Gets context information for the event.
     *
     * @param string|null $name The name of the context item to retrieve or set.
     * @return mixed If $name is null, returns the entire context array.
     *               If $name is specified, returns the value associated with that name.
     */
    public function getContext(string $name = null): mixed;
}
