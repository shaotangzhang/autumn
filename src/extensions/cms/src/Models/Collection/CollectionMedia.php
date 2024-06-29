<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Collection;

use Autumn\Database\Traits\RelationManagerTrait;
use Autumn\Extensions\Cms\Models\Media\MediaRelation;
use Autumn\Extensions\Cms\Models\Traits\CollectionIdColumnTrait;

class CollectionMedia extends MediaRelation
{
    use RelationManagerTrait;
    use CollectionIdColumnTrait;

    public const ENTITY_NAME = 'cms_collection_media';

    public const ENTITY_PRIMARY_COLUMN = 'collection_id';

    public const ENTITY_PRIMARY_CLASS = Collection::class;
}