<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Menu;

use Autumn\Extensions\Cms\Models\Traits\IconColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\TargetColumnTrait;
use Autumn\Extensions\Cms\Models\Widget\WidgetEntity;

class MenuEntity extends WidgetEntity
{
    use IconColumnTrait;
    use TargetColumnTrait;

    public const ENTITY_NAME = 'cms_menus';
}