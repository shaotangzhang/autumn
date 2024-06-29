<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Collection;

use Autumn\Database\Models\AbstractEntity;
use Autumn\Extensions\Cms\Models\Page\PageEntity;
use Autumn\Extensions\Cms\Models\Traits\SiteIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\SlugifyUrlTrait;

class CollectionEntity extends PageEntity
{
    use SiteIdColumnTrait;
    use SlugifyUrlTrait;

    public const ENTITY_NAME = 'cms_collections';

    public static function defaultUrlPrefix(): ?string
    {
        return env('CMS_URL_COLLECTIONS_PREFIX', '/collections/');
    }

    public static function defaultUrlSuffix(): ?string
    {
        return env('CMS_URL_COLLECTIONS_SUFFIX');
    }
}