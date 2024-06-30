<?php

namespace Autumn\System\Templates;

use Autumn\Interfaces\Renderable;
use Autumn\Lang\HTML;
use Autumn\System\View;

class Component implements Renderable, \Stringable
{
    private string $tagName;
    private array $attributes;
    private iterable $children;
    private string $textContent = '';
    private ?array $context = null;

    private ?View $view = null;

    public function __construct(string $tagName, array $attributes = null, array $context = null, mixed ...$children)
    {
        $this->tagName = $tagName;
        $this->attributes = $attributes ?? [];
        $this->children = $children;
        $this->context = $context;
    }

    public static function fragment(mixed $content): static
    {
        if ($content instanceof static) {
            return $content;
        }

        $instance = new static('');
        $instance->children[] = $content;
        return $instance;
    }

    public static function stack(string $tagName, array $attributes = null, mixed ...$children): static
    {
        $instance = new static($tagName, $attributes);
        $instance->append(...$children);
        return $instance;
    }

    /**
     * @return View|null
     */
    public function getView(): ?View
    {
        return $this->view;
    }

    /**
     * @param View|null $view
     */
    public function setView(?View $view): void
    {
        $this->view = $view;
    }

    public function __toString(): string
    {
        ob_start();

        try {
            $this->render();
            return ob_get_contents();
        } catch (\Throwable $ex) {
            if (env('DEBUG')) {
                return (string)$ex;
            }
            return '';
        } finally {
            ob_end_clean();
        }
    }

    public function render(): void
    {
        if ($this->view) {
//            $this->view->setContents(fn() => HTML::outputElement(
//                $this->tagName,
//                $this->attributes,
//                $this->context,
//                $this->children,
//                $this->textContent
//            ));
            $this->view->with($this->attributes);
            $this->view->with(['children'=>$this->children]);
            TemplateService::context()->outputView($this->view, $this->context);
        } else {
            HTML::outputElement(
                $this->tagName,
                $this->attributes,
                $this->context,
                $this->children,
                $this->textContent
            );
        }
    }

    public function append(mixed ...$args): void
    {
        foreach ($args as $arg) {
            if ($arg === null || $arg === false || $arg === [] || $arg === '') {
                continue;
            }

            if ($arg instanceof self) {
                $this->children[] = $arg;
                continue;
            }

            if (!is_array($arg)) {
                $this->children[] = static::fragment($arg);
                continue;
            }

            if (is_string($arg[0])) {
                $child = new static($arg[0]);
                unset($arg[0]);
                $child->fill($arg);
                $this->children[] = $child;
                continue;
            }

            $this->append(...$arg);
        }
    }

    public function fill(array $args): void
    {
        foreach ($args as $index => $arg) {
            if ($arg === null || $arg === false || $arg === [] || $arg === '') {
                continue;
            }

            if (is_string($index)) {
                $this->attributes[$index] = $arg;
                continue;
            }

            if ($arg instanceof self) {
                $this->children[] = $arg;
                continue;
            }

            if (!is_array($arg)) {
                $this->children[] = static::fragment($arg);
                continue;
            }

            if (is_string($arg[0])) {
                $child = new static($arg[0]);
                unset($arg[0]);
                $child->fill($arg);
                $this->children[] = $child;
                continue;
            }

            $this->append(...$arg);
        }
    }

    public function prependChild(self $child): static
    {
        array_unshift($this->children, $child);
        return $this;
    }

    public function appendChild(self $child): static
    {
        $this->children[] = $child;
        return $this;
    }
}