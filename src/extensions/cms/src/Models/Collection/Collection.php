<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Collection;

use Autumn\Database\Interfaces\RecyclableRepositoryInterface;
use Autumn\Database\Traits\RecyclableEntityManagerTrait;
use Autumn\Extensions\Cms\Models\Traits\MultipleSitesRepositoryTrait;

class Collection extends CollectionEntity implements RecyclableRepositoryInterface
{
    use RecyclableEntityManagerTrait;
    use MultipleSitesRepositoryTrait;
}