<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Media;

use Autumn\Database\Attributes\Index;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Traits\EntityManagerTrait;
use Autumn\Database\Traits\ExtendedEntityTrait;
use Autumn\Extensions\Cms\Models\Meta\MetaEntity;
use Autumn\Extensions\Cms\Models\Meta\MetaManagerTrait;
use Autumn\Extensions\Cms\Models\Traits\MediaIdColumnTrait;

#[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE, self::RELATION_PRIMARY_COLUMN, 'type', 'name', 'code')]
class MediaMeta extends MetaEntity implements RepositoryInterface
{
    use MetaManagerTrait;
    use MediaIdColumnTrait;

    public const ENTITY_NAME = 'cms_media_meta';

    public const RELATION_PRIMARY_CLASS = Media::class;
    public const RELATION_PRIMARY_COLUMN = 'media_id';
}