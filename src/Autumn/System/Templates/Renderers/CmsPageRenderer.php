<?php

namespace Autumn\System\Templates\Renderers;

use ArrayAccess;
use Autumn\System\Templates\RendererInterface;
use Autumn\System\View;

class CmsPageRenderer implements RendererInterface
{
    public function output(mixed $data, ArrayAccess|array $args = null, array $context = null): mixed
    {
        return $data;
    }

//    /**
//     * Outputs the rendered view for a given data type.
//     *
//     * @param mixed $data The data to be rendered, which could be a Page, PageWidget, or PageEntity.
//     * @param ArrayAccess|array|null $args Optional arguments to pass to the view.
//     * @param array|null $context The rendering context.
//     * @return mixed
//     */
//    public function output(mixed $data, ArrayAccess|array $args = null, array $context = null): mixed
//    {
//        if ($data instanceof Page) {
//            $this->createPageView($data, $args, $context)?->render();
//            return null;
//        }
//
//        if ($data instanceof PageWidget) {
//            $this->createWidgetView($data, $args, $context)?->render();
//            return null;
//        }
//
//        if ($data instanceof PageEntity) {
//            $this->createStandardPageView($data, $args, $context)?->render();
//            return null;
//        }
//
//        return $data;
//    }
//
//    /**
//     * Creates a view for a Page instance.
//     *
//     * @param Page $page The Page instance.
//     * @param ArrayAccess|array|null $args Optional arguments to pass to the view.
//     * @param array|null $context The rendering context.
//     * @return View
//     */
//    public function createPageView(Page $page, ArrayAccess|array $args = null, array $context = null): View
//    {
//        if ($args) {
//            $args = array_merge($page->toArray(), $args);
//        } else {
//            $args = $page->toArray();
//        }
//
//        $widgets = [];
//
//        if ($layout = $page->getLayout()) {
//            $view = new View($layout->getTemplate(), $args, $context);
//            foreach ($layout->widgets() as $widget) {
//                $widgets[] = $widget;
//            }
//        } else {
//            $view = new View($page->getTemplate(), $args, $context);
//        }
//
//        foreach ($page->widgets() as $widget) {
//            $widgets[] = $widget;
//        }
//
//        uasort($widgets, fn($a, $b) => $a->getSortOrder() <=> $b->getSortOrder());
//
//        foreach ($widgets as $widget) {
//            $view->defineSlot($widget->getSlot(), fn($args, $context) => $this->createWidgetView($widget, $args, $context));
//        }
//
//        return $view;
//    }
//
//    /**
//     * Creates a view for a PageWidget instance.
//     *
//     * @param PageWidget $pageWidget The PageWidget instance.
//     * @param ArrayAccess|array|null $args Optional arguments to pass to the view.
//     * @param array|null $context The rendering context.
//     * @return View|null
//     */
//    public function createWidgetView(PageWidget $pageWidget, ArrayAccess|array $args = null, array $context = null): ?View
//    {
//        if ($widget = $pageWidget->widget()) {
//            $context[PageWidget::class] = $pageWidget;
//            $template = '/widgets/' . strtr($widget->getType(), '_', '/') . '/default';
//            return new View($template, $args, $context);
//        }
//        return null;
//    }
//
//    /**
//     * Creates a standard view for a PageEntity instance.
//     *
//     * @param PageEntity $page The PageEntity instance.
//     * @param ArrayAccess|array|null $args Optional arguments to pass to the view.
//     * @param array|null $context The rendering context.
//     * @return View|null
//     */
//    public function createStandardPageView(PageEntity $page, ArrayAccess|array $args = null, array $context = null): ?View
//    {
//        $template = ($page::entity_view_path() ?: strtr($page::entity_name(), '_', '/')) . '/' . $page->getTemplate();
//
//        if ($args) {
//            $args = array_merge($page->toArray(), $args);
//        } else {
//            $args = $page->toArray();
//        }
//
//        return new View($template, $args, $context);
//    }
}