<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Page;

use Autumn\Database\Attributes\Index;
use Autumn\Database\Interfaces\Expirable;
use Autumn\Database\Interfaces\StatusInterface;
use Autumn\Database\Interfaces\TypeInterface;
use Autumn\Database\Models\RecyclableEntity;
use Autumn\Database\Traits\DescriptionColumnTrait;
use Autumn\Database\Traits\ExpirableTrait;
use Autumn\Database\Traits\StatusColumnTrait;
use Autumn\Database\Traits\TypeColumnTrait;
use Autumn\Extensions\Cms\Interfaces\MetaSupportedInterface;
use Autumn\Extensions\Cms\Interfaces\Parentable;
use Autumn\Extensions\Cms\Interfaces\Publishable;
use Autumn\Extensions\Cms\Interfaces\Sortable;
use Autumn\Extensions\Cms\Models\Traits\ContentColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\ImageColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\LangColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\LayoutIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\LinkColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\ParentIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\PublishedAtColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\SeoColumnsTrait;
use Autumn\Extensions\Cms\Models\Traits\SlugifyUrlTrait;
use Autumn\Extensions\Cms\Models\Traits\SortOrderColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\TemplateColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\TitleColumnTrait;

// status, slug, deleted_at, sort_order, id

#[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE, 'parent_id', 'type', 'lang', 'slug')]
#[Index(Index::DEFAULT_INDEX_NAME, Index::INDEX, 'parent_id', 'type', 'lang', 'status', 'sort_order', 'published_at', 'expired_at', 'created_at', 'updated_at', 'deleted_at')]
abstract class PageEntity extends RecyclableEntity
    implements Expirable, Sortable, Parentable, Publishable, StatusInterface, TypeInterface
{
    use ParentIdColumnTrait;
    use LayoutIdColumnTrait;

    use TitleColumnTrait;
    use DescriptionColumnTrait;
    use ContentColumnTrait;

    use ImageColumnTrait;
    use LinkColumnTrait;
    use SlugifyUrlTrait;

    use SeoColumnsTrait;

    use LangColumnTrait;
    use TypeColumnTrait;
    use StatusColumnTrait;
    use TemplateColumnTrait;

    use SortOrderColumnTrait;
    use PublishedAtColumnTrait;
    use ExpirableTrait;

    public const ENTITY_NAME = 'cms_pages';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_DISABLED = 'disabled';
    public const STATUS_PENDING = 'pending';
    public const STATUS_TRASHED = 'trashed';

    public const DEFAULT_TYPE = 'default';
    public const DEFAULT_STATUS = self::STATUS_ACTIVE;

    public const COLUMN_AS_NAME = 'slug';

    public static function entity_column_as_name(): ?string
    {
        return static::COLUMN_AS_NAME;
    }

    public static function entity_available_statuses(): array
    {
        return [static::STATUS_ACTIVE, static::STATUS_PENDING, static::STATUS_DISABLED];
    }

    public function getLayout(): ?string
    {
        if ($this instanceof MetaSupportedInterface) {
            return $this->metadata('layout', $this->lang);
        }

        return null;
    }
}