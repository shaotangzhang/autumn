<?php
/**
 * Autumn PHP Framework
 *
 * Date:        13/05/2024
 */

namespace Autumn\Extensions\Cms\Models\Site;

use Autumn\Database\Interfaces\RecyclableRepositoryInterface;
use Autumn\Database\Traits\RecyclableEntityManagerTrait;

class Site extends SiteEntity implements RecyclableRepositoryInterface
{
    use RecyclableEntityManagerTrait;
}