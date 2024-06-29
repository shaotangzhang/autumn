<?php
/**
 * Autumn PHP Framework
 *
 * Date:        10/06/2024
 */

namespace Autumn\Extensions\Cms\Models\Tag;

use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Models\Relation;
use Autumn\Database\Traits\RelationManagerTrait;
use Autumn\Extensions\Cms\Models\Traits\TagIdColumnTrait;

class TagRelation extends Relation implements RepositoryInterface
{
    use RelationManagerTrait;
    use TagIdColumnTrait;

    public const RELATION_SECONDARY_COLUMN = 'tag_id';
    public const RELATION_SECONDARY_CLASS = Tag::class;
}