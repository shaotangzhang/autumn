<?php
/**
 * Autumn PHP Framework
 *
 * Date:        24/05/2024
 */

namespace Autumn\Extensions\Cms\Models\Widget;

use Autumn\Database\Attributes\Index;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Extensions\Cms\Models\Meta\MetaEntity;
use Autumn\Extensions\Cms\Models\Meta\MetaManagerTrait;
use Autumn\Extensions\Cms\Models\Traits\WidgetIdColumnTrait;

#[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE, self::RELATION_PRIMARY_COLUMN, 'type', 'name', 'code')]
class WidgetMeta extends MetaEntity implements RepositoryInterface
{
    use MetaManagerTrait;
    use WidgetIdColumnTrait;

    public const ENTITY_NAME = 'cms_widget_meta';
    public const RELATION_PRIMARY_CLASS = Widget::class;
    public const RELATION_PRIMARY_COLUMN = 'widget_id';
}