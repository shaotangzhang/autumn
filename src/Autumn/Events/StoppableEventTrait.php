<?php
/**
 * Autumn PHP Framework
 *
 * Date:        13/02/2024
 */

namespace Autumn\Events;

use Autumn\Attributes\Transient;

trait StoppableEventTrait
{
    #[Transient]
    private bool $propagationStopped = false;

    #[Transient]
    private array $context = [];

    public function getContext(string $name = null): mixed
    {
        return $name ? $this->context[$name] ?? null : $this->context;
    }

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }


}