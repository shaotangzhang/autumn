<?php

namespace Autumn\Extensions\Cms\Views;

use Autumn\System\Templates\TemplateService;
use Autumn\System\View;

class PageView extends View
{
    private ?LayoutView $layout = null;

    public function __construct(private readonly PageEntity $entity, ?array $context = null)
    {
        $context[PageEntity::class] = $this->entity;
        parent::__construct($this->entity->getTemplate(), $this->entity->toArray(), $context);
    }

    public function getLayout(): ?LayoutView
    {
        if ($this->entity instanceof Page) {
            $context = $this->getContext();
            $context[Page::class] = $this->entity;
            return $this->layout ??= new LayoutView($this->entity->layout(), $context);
        }

        return null;
    }

    public function render(): void
    {
        app(TemplateService::class)->renderPage($this);
    }
}