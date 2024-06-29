<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Author;

use Autumn\Database\Interfaces\TypeInterface;
use Autumn\Database\Models\Relation;
use Autumn\Database\Traits\RelationTrait;
use Autumn\Database\Traits\TypeColumnTrait;
use Autumn\Extensions\Cms\Interfaces\Sortable;
use Autumn\Extensions\Cms\Models\Media\MediaEntity;
use Autumn\Extensions\Cms\Models\Traits\AuthorIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\MediaIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\SortOrderColumnTrait;

class AuthorMedia extends Relation implements Sortable, TypeInterface
{
    use RelationTrait;
    use TypeColumnTrait;
    use AuthorIdColumnTrait;
    use MediaIdColumnTrait;
    use SortOrderColumnTrait;

    public const ENTITY_NAME = 'cms_author_media';
    public const DEFAULT_TYPE = 'default';

    public const ENTITY_PRIMARY_COLUMN = 'author_id';
    public const ENTITY_SECONDARY_COLUMN = 'media_id';

    public const ENTITY_PRIMARY_CLASS = Author::class;
    public const ENTITY_SECONDARY_CLASS = MediaEntity::class;
}