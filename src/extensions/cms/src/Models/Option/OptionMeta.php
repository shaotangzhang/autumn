<?php
/**
 * Autumn PHP Framework
 *
 * Date:        17/02/2024
 */

namespace Autumn\Extensions\Cms\Models\Option;

use Autumn\Database\Attributes\Index;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Traits\EntityManagerTrait;
use Autumn\Database\Traits\ExtendedEntityTrait;
use Autumn\Extensions\Cms\Models\Meta\MetaEntity;
use Autumn\Extensions\Cms\Models\Meta\MetaManagerTrait;
use Autumn\Extensions\Cms\Models\Traits\OptionIdColumnTrait;

#[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE, self::RELATION_PRIMARY_COLUMN, 'type', 'name', 'code')]
class OptionMeta extends MetaEntity implements RepositoryInterface
{
    use MetaManagerTrait;
    use OptionIdColumnTrait;

    public const ENTITY_NAME = 'cms_option_meta';
    public const RELATION_PRIMARY_CLASS = Option::class;
    public const RELATION_PRIMARY_COLUMN = 'option_id';
}