<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Media;

use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Traits\RelationManagerTrait;
use Autumn\Extensions\Cms\Models\Collection\Collection;
use Autumn\Extensions\Cms\Models\Collection\CollectionRelation;
use Autumn\Extensions\Cms\Models\Traits\CollectionIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\MediaIdColumnTrait;
use Autumn\Database\Models\Relation;

class MediaCollection extends CollectionRelation
{
    use RelationManagerTrait;
    use MediaIdColumnTrait;

    public const ENTITY_NAME = 'cms_media_collections';
    public const RELATION_PRIMARY_COLUMN = 'media_id';
    public const RELATION_PRIMARY_CLASS = Media::class;
}