<?php

namespace Autumn\Extensions\Cms\Views;

use Autumn\System\Templates\TemplateService;

class WidgetView extends PageView
{
    public function __construct(Widget $entity, ?array $context = null)
    {
        parent::__construct($entity, $context);
    }

    public function render(): void
    {
        app(TemplateService::class)->renderWidget($this);
    }
}