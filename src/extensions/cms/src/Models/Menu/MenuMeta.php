<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Menu;

use Autumn\Database\Attributes\Index;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Extensions\Cms\Models\Meta\MetaEntity;
use Autumn\Extensions\Cms\Models\Meta\MetaManagerTrait;

#[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE, self::RELATION_PRIMARY_COLUMN, 'type', 'name', 'code')]
class MenuMeta extends MetaEntity implements RepositoryInterface
{
    use MetaManagerTrait;

    public const ENTITY_NAME = 'cms_menu_meta';

    public const RELATION_PRIMARY_CLASS = Menu::class;
    public const RELATION_PRIMARY_COLUMN = 'menu_id';
}