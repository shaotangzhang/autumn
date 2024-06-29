<?php
/**
 * Autumn PHP Framework
 *
 * Date:        10/05/2024
 */

namespace Autumn\Extensions\Cms\Models\Media;

use Autumn\Database\Interfaces\RecyclableRepositoryInterface;
use Autumn\Database\Traits\RecyclableEntityManagerTrait;
use Autumn\Extensions\Cms\Models\Traits\MultipleSitesRepositoryTrait;

class Media extends MediaEntity implements RecyclableRepositoryInterface
{
    use RecyclableEntityManagerTrait;
    use MultipleSitesRepositoryTrait;
}