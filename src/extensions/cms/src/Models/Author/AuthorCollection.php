<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Author;

use Autumn\Database\Traits\RelationManagerTrait;
use Autumn\Extensions\Cms\Models\Collection\CollectionRelation;
use Autumn\Extensions\Cms\Models\Traits\AuthorIdColumnTrait;

class AuthorCollection extends CollectionRelation
{
    use RelationManagerTrait;
    use AuthorIdColumnTrait;

    public const ENTITY_NAME = 'cms_author_collections';
    public const ENTITY_PRIMARY_COLUMN = 'author_id';
    public const ENTITY_PRIMARY_CLASS = Author::class;
}