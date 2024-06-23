<?php
/**
 * Autumn PHP Framework
 *
 * Date:        17/06/2024
 */

namespace Autumn\System;

use Autumn\Interfaces\ArrayInterface;
use Autumn\Interfaces\Renderable;
use Autumn\System\Templates\TemplateService;

class View implements ArrayInterface, Renderable, \ArrayAccess
{
    private array $slots = [];

    private mixed $contents = null;

    private ?TemplateService $templateService = null;

    public function __construct(private string $name,
                                private ?array $args = null,
                                private ?array $context = null
    )
    {
    }

    public function __get(string $name): mixed
    {
        return $this->args[$name] ?? null;
    }

    /**
     * @return TemplateService|null
     */
    public function getTemplateService(): ?TemplateService
    {
        return $this->templateService ??= app(TemplateService::class);
    }

    /**
     * @param TemplateService|null $templateService
     */
    public function setTemplateService(?TemplateService $templateService): void
    {
        $this->templateService = $templateService;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function with(array $data): static
    {
        $this->args = array_merge($this->args, $data);
        return $this;
    }

    public function toArray(): array
    {
        return $this->args ?? [];
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context ??= [];
    }

    public function contents(): void
    {
        $this->getTemplateService()?->render($this->contents, $this, $this->context);
    }

    /**
     * @return mixed
     */
    public function getContents(): mixed
    {
        return $this->contents;
    }

    /**
     * @param mixed $contents
     */
    public function setContents(mixed $contents): void
    {
        $this->contents = $contents;
    }

    public function render(): void
    {
        $this->getTemplateService()?->renderView($this);
    }

    public function defineLayoutSlot(string $slot, callable $definition): void
    {
        $this->context['use_slots'][strtolower($slot)][] = $definition;
    }

    public function defineSlot(string $slot, callable $definition): void
    {
        $this->slots[strtolower($slot)][] = $definition;
    }

    public function slot(string $slot, array $args = null, array $context = null): void
    {
        $context[View::class] = $this;
        foreach ($this->slots[strtolower($slot)] ?? [] as $callback) {
            // $result = call($callback, $args, $context);
            $this->getTemplateService()?->output($callback, $args, $context);
        }
    }

    public function can(string $name, bool $default = false): bool
    {
        return filter_var($this->context[$name] ?? $default, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)
            ?? $default;
    }

    public function has(string $name): bool
    {
        return isset($this->args[$name]);
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->args[$name] ?? $default;
    }

    public function set(string $name, mixed $value, bool $onlyIfNotSet = false): void
    {
        if ($onlyIfNotSet) {
            $this->args[$name] ??= $value;
        } else {
            $this->args[$name] = $value;
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->args[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->args[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->args[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->args[$offset]);
    }
}