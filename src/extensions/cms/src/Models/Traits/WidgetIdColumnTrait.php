<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Traits;

use Autumn\Database\Attributes\Column;

trait WidgetIdColumnTrait
{
    #[Column(type: Column::FK, name: 'widget_id', unsigned: true)]
    private int $widgetId = 0;

    /**
     * @return int
     */
    public function getWidgetId(): int
    {
        return $this->widgetId;
    }

    /**
     * @param int $widgetId
     */
    public function setWidgetId(int $widgetId): void
    {
        $this->widgetId = $widgetId;
    }
}