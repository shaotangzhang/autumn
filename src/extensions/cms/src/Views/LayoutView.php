<?php

namespace Autumn\Extensions\Cms\Views;

use Autumn\System\Templates\TemplateService;

class LayoutView extends PageView
{
    private mixed $contents = null;

    public function __construct(Layout $entity, ?array $context = null)
    {
        $context[PageEntity::class] = $entity;
        parent::__construct($entity, $context);
    }

    public function contents(): void
    {
        app(TemplateService::class)
            ->renderContents($this->contents ?? $this['content']);
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

    public function getPage(): ?Page
    {
        return $this->getContext()[Page::class] ?? null;
    }

    public function render(): void
    {
        app(TemplateService::class)->renderLayout($this);
    }
}