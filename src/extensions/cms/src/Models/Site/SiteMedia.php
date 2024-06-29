<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Post;


use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Interfaces\TypeInterface;
use Autumn\Database\Models\Relation;
use Autumn\Database\Traits\RelationManagerTrait;
use Autumn\Database\Traits\TypeColumnTrait;
use Autumn\Extensions\Cms\Interfaces\Sortable;
use Autumn\Extensions\Cms\Models\Media\Media;
use Autumn\Extensions\Cms\Models\Media\MediaRelation;
use Autumn\Extensions\Cms\Models\Site\Site;
use Autumn\Extensions\Cms\Models\Traits\MediaIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\SiteIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\SortOrderColumnTrait;

class SiteMedia extends MediaRelation
{
    use RelationManagerTrait;
    use SiteIdColumnTrait;

    public const ENTITY_NAME = 'cms_site_media';
    public const RELATION_PRIMARY_COLUMN = 'site_id';
    public const RELATION_PRIMARY_CLASS = Site::class;
}