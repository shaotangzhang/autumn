<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Widget;

use Autumn\Database\Traits\RelationManagerTrait;
use Autumn\Extensions\Cms\Models\Media\MediaRelation;
use Autumn\Extensions\Cms\Models\Traits\WidgetIdColumnTrait;

class WidgetMedia extends MediaRelation
{
    use RelationManagerTrait;
    use WidgetIdColumnTrait;

    public const ENTITY_NAME = 'cms_widget_media';

    public const RELATION_PRIMARY_COLUMN = 'widget_id';

    public const RELATION_PRIMARY_CLASS = Widget::class;
}