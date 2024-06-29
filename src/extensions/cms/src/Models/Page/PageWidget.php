<?php
/**
 * Autumn PHP Framework
 *
 * Date:        27/06/2024
 */

namespace Autumn\Extensions\Cms\Models\Page;

use Autumn\Database\Models\Relation;
use Autumn\Extensions\Cms\Models\Traits\ConfigColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\PageIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\WidgetIdColumnTrait;
use Autumn\Extensions\Cms\Models\Widgets\Widget;
use Autumn\System\View;

class PageWidget extends Relation
{
    use PageIdColumnTrait;
    use WidgetIdColumnTrait;
    use ConfigColumnTrait;

    public const ENTITY_NAME = 'cms_page_widgets';
    public const RELATION_PRIMARY_COLUMN = 'page_id';
    public const RELATION_SECONDARY_COLUMN = 'widget_id';
    public const RELATION_PRIMARY_CLASS = Page::class;
    public const RELATION_SECONDARY_CLASS = Widget::class;

    public function page(): Page
    {
        return $this->hasOne(Page::class, static::RELATION_PRIMARY_COLUMN);
    }

    public function widget(): Widget
    {
        return $this->hasOne(Widget::class, static::RELATION_SECONDARY_COLUMN);
    }

    public function createView(): ?View
    {
        $widget = $this->widget();
        $widgetType = $widget?->getType();
        if (empty($widgetType)) {
            return null;
        }

        $template = '/widgets/' . trim(strtr($widgetType, '_', '/'), '/') . '/default';

        $context = [PageWidget::class => $this];

        $args = $this->toArray();
        $args['context'] = $context;
        $args['use_layout'] = false;

        return new View($template, $args, $context);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (!is_string($offset) || !trim($offset)) {
            return null;
        }

        return parent::offsetGet($offset)
            ?? $this->config($offset)
            ?? $this->widget()?->offsetGet($offset);
    }
}