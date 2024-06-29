<?php
/**
 * Autumn PHP Framework
 *
 * Date:        27/06/2024
 */

namespace Autumn\Extensions\Cms\Models\Widgets;

use Autumn\Database\Traits\TypeColumnTrait;
use Autumn\Extensions\Cms\Models\Page\PageEntity;

class WidgetEntity extends PageEntity
{
    use TypeColumnTrait;

    public const DEFAULT_TYPE = 'widgets';
}