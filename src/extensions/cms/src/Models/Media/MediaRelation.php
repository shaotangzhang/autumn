<?php
/**
 * Autumn PHP Framework
 *
 * Date:        10/06/2024
 */

namespace Autumn\Extensions\Cms\Models\Media;

use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Interfaces\TypeInterface;
use Autumn\Database\Models\Relation;
use Autumn\Database\Traits\TypeColumnTrait;
use Autumn\Extensions\Cms\Interfaces\Sortable;
use Autumn\Extensions\Cms\Models\Traits\MediaIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\SortOrderColumnTrait;

abstract class MediaRelation extends Relation implements Sortable, TypeInterface, RepositoryInterface
{
    use MediaIdColumnTrait;
    use SortOrderColumnTrait;
    use TypeColumnTrait;

    public const DEFAULT_TYPE = 'default';
    public const RELATION_SECONDARY_COLUMN = 'media_id';
    public const RELATION_SECONDARY_CLASS = Media::class;
}