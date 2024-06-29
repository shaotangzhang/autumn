<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Author;

use Autumn\Database\Attributes\Index;
use Autumn\Database\Models\RecyclableEntity;
use Autumn\Database\Traits\DescriptionColumnTrait;
use Autumn\Database\Traits\StatusColumnTrait;
use Autumn\Database\Traits\TypeColumnTrait;
use Autumn\Extensions\Auth\Models\Traits\UserIdColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\AvatarColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\ContentColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\EmailColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\LinkColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\NicknameColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\SeoColumnsTrait;
use Autumn\Extensions\Cms\Models\Traits\SlugColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\SortOrderColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\TemplateColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\TitleColumnTrait;

#[Index(Index::DEFAULT_INDEX_NAME, Index::INDEX, 'user_id')]
#[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE, 'user_id', 'email')]
class AuthorEntity extends RecyclableEntity
{
    /**
     * The linked user ID of the author
     */
    use UserIdColumnTrait;

    /**
     * The author's title
     */
    use TitleColumnTrait;

    /**
     * The author's nickname
     */
    use NicknameColumnTrait;

    /**
     * The author's email
     */
    use EmailColumnTrait;

    /**
     * The author's bio-description
     */
    use DescriptionColumnTrait;

    /**
     * The author's introduction
     */
    use ContentColumnTrait;

    /**
     * The author's avatar
     */
    use AvatarColumnTrait;

    /**
     * The author's website url
     */
    use LinkColumnTrait;

    /**
     * The author's slug name for the access of personal profile
     */
    use SlugColumnTrait;

    /**
     * For page SEO settings
     */
    use SeoColumnsTrait;

    /**
     * The type of author: such as individual or organization
     */
    use TypeColumnTrait;

    /**
     * The current status of the author
     */
    use StatusColumnTrait;

    /**
     * The template for displaying the profile
     */
    use TemplateColumnTrait;

    /**
     * The sorting order of the author, could be used as priority level
     */
    use SortOrderColumnTrait;

    public const ENTITY_NAME = 'cms_authors';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_DISABLED = 'disabled';
    public const STATUS_PENDING = 'pending';

    public const DEFAULT_TYPE = 'default';
    public const DEFAULT_STATUS = self::STATUS_ACTIVE;

}