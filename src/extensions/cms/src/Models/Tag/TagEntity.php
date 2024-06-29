<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Tag;

use Autumn\Database\Models\RecyclableEntity;
use Autumn\Extensions\Cms\Models\Traits\SlugifyUrlTrait;
use Autumn\Extensions\Cms\Models\Traits\TitleColumnTrait;

class TagEntity extends RecyclableEntity
{
    use TitleColumnTrait;
    use SlugifyUrlTrait;

    public const ENTITY_NAME = 'cms_tags';
}