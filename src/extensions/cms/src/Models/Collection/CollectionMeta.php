<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Collection;

use Autumn\Database\Attributes\Index;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Extensions\Cms\Models\Meta\MetaEntity;
use Autumn\Extensions\Cms\Models\Meta\MetaManagerTrait;
use Autumn\Extensions\Cms\Models\Traits\CollectionIdColumnTrait;

#[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE, self::RELATION_PRIMARY_COLUMN, 'name')]
class CollectionMeta extends MetaEntity implements RepositoryInterface
{
    use MetaManagerTrait;
    use CollectionIdColumnTrait;

    public const ENTITY_NAME = 'cms_collection_meta';

    public const ENTITY_PRIMARY_CLASS = Collection::class;
}