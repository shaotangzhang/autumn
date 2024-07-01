<?php
/**
 * Autumn PHP Framework
 *
 * Date:        17/06/2024
 */

namespace Autumn\System;

use Autumn\I18n\Translatable;
use Autumn\I18n\Translation;
use Autumn\Interfaces\ArrayInterface;
use Autumn\Interfaces\Renderable;
use Autumn\System\Templates\TemplateService;

class View implements ArrayInterface, Renderable, \ArrayAccess, Translatable
{
    private array $slots = [];

    private mixed $contents = null;

    private ?TemplateService $templateService = null;

    private ?Translation $translation = null;

    private string $_template_name_;

    public function __construct(string         $name,
                                private ?array $args = null,
                                private ?array $context = null
    )
    {
        $this->_template_name_ = $name;
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
        return $this->templateService ??= make(TemplateService::class);
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
        return $this->_template_name_;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->_template_name_ = $name;
    }

    public function with(array $data): static
    {
        $this->args = array_merge($this->args ?? [], $data);
        return $this;
    }

    public function toArray(): array
    {
        return $this->args ?: [];
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

    /**
     * @return Translation|null
     */
    public function getTranslation(): ?Translation
    {
        return $this->translation;
    }

    /**
     * @param Translation|null $translation
     */
    public function setTranslation(?Translation $translation): void
    {
        $this->translation = $translation;
    }

    public function loadTranslation(string $path, string $prefix = null, string $lang = null): void
    {
        if ($translations = Translation::load($path, $prefix, $lang)) {
            if ($this->translation) {
                $this->translation->merge($translations);
            } else {
                $this->translation = $translations;
            }
        }
    }

    public function render(): void
    {
        $this->getTemplateService()?->renderView($this);
    }

    public function defineLayoutSlot(string $slot, callable $definition): void
    {
        $this->context['use_slots'][strtolower($slot)][] = $definition;
    }

    public function defineLayoutImport(string $src, string $type = null, array $context = null): void
    {
        if (!$type) {
            $type = strtolower(pathinfo(explode('?', $src)[0], PATHINFO_EXTENSION));
        }

        $this->context['use_imports'][$type][$src] = $context;
    }

    public function defineSlot(string $slot, callable $definition): static
    {
        $this->slots[strtolower($slot)][] = $definition;
        return $this;
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

    public function translate(string $text, ...$args): ?string
    {
        return $this->getTranslation()?->format(...func_get_args()) ?? $text;
    }
}