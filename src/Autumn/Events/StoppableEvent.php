<?php
/**
 * Autumn PHP Framework
 *
 * Date:        13/02/2024
 */

namespace Autumn\Events;

use Autumn\Attributes\Transient;

class StoppableEvent implements StoppableEventInterface
{
    #[Transient]
    private bool $propagationStopped = false;

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

}