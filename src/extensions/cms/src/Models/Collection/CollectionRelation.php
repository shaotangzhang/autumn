<?php
/**
 * Autumn PHP Framework
 *
 * Date:        10/06/2024
 */

namespace Autumn\Extensions\Cms\Models\Collection;

use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Models\Relation;
use Autumn\Extensions\Cms\Models\Traits\CollectionIdColumnTrait;

abstract class CollectionRelation extends Relation implements RepositoryInterface
{
    use CollectionIdColumnTrait;

    public const RELATION_PRIMARY_COLUMN = 'collection_id';
    public const RELATION_PRIMARY_CLASS = Collection::class;
}