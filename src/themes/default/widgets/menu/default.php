<?php

/**
 * The default template of Menu Widget
 */

use Autumn\Extensions\Cms\Models\Page\PageWidget;

return function (PageWidget $pageWidget, array $context = null) {

    $template = $pageWidget['template'];
    if (realpath($file = __DIR__ . '/' . $template . '.php')) {
        return (include $file)($pageWidget, $context);
    }

    $widget = $pageWidget->widget();

    // ***************
    // main part of the widget
    // ***************

    return null;
};