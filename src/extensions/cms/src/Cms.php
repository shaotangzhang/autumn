<?php
/**
 * Autumn PHP Framework
 *
 * Date:        23/06/2024
 */

namespace Autumn\Extensions\Cms;

use Autumn\Extensions\Auth\Auth;
use Autumn\Extensions\Cms\Models\Author\Author;
use Autumn\Extensions\Cms\Models\Category\Category;
use Autumn\Extensions\Cms\Models\Collection\Collection;
use Autumn\Extensions\Cms\Models\Media\Media;
use Autumn\Extensions\Cms\Models\Menu\Menu;
use Autumn\Extensions\Cms\Models\Message\Message;
use Autumn\Extensions\Cms\Models\Meta\Meta;
use Autumn\Extensions\Cms\Models\Option\Option;
use Autumn\Extensions\Cms\Models\Page\Page;
use Autumn\Extensions\Cms\Models\Post\Post;
use Autumn\Extensions\Cms\Models\Site\Site;
use Autumn\Extensions\Cms\Models\Tag\Tag;
use Autumn\Extensions\Cms\Models\Widgets\Widget;
use Autumn\System\Application;
use Autumn\System\Extension;

class Cms extends Extension
{
    /**
     * The version of the CMS extension.
     */
    public const VERSION = "1.0.0";

    public const REQUIRED_EXTENSIONS = [
        Auth::class
    ];

    public const REGISTERED_ENTITIES = [
        Page::class,

        Author::class,

        Category::class,

        Collection::class,

        Media::class,

        Menu::class,

        Message::class,

        Meta::class,

        Option::class,

        Post::class,

        Site::class,

        Tag::class,

        Widget::class,
    ];

    public static function boot(Application $application): void
    {
        parent::boot($application);
    }
}