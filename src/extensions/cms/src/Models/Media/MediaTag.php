<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Media;

use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Traits\RelationManagerTrait;
use Autumn\Extensions\Cms\Models\Tag\Tag;
use Autumn\Extensions\Cms\Models\Tag\TagRelation;
use Autumn\Extensions\Cms\Models\Traits\MediaIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\PageIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\TagIdColumnTrait;
use Autumn\Database\Models\Relation;

class MediaTag extends TagRelation
{
    use RelationManagerTrait;
    use MediaIdColumnTrait;

    public const ENTITY_NAME = 'cms_media_tags';

    public const RELATION_PRIMARY_COLUMN = 'media_id';

    public const RELATION_PRIMARY_CLASS = Media::class;
}