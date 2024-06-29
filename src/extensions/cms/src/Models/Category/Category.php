<?php
/**
 * Autumn PHP Framework
 *
 * Date:        10/05/2024
 */

namespace Autumn\Extensions\Cms\Models\Category;

use Autumn\Database\Interfaces\RecyclableRepositoryInterface;
use Autumn\Database\Models\AbstractEntity;
use Autumn\Database\Traits\RecyclableEntityManagerTrait;
use Autumn\Extensions\Cms\Models\Traits\ItemsTrait;
use Autumn\Extensions\Cms\Models\Traits\MultipleSitesRepositoryTrait;

class Category extends CategoryEntity implements RecyclableRepositoryInterface
{
    use RecyclableEntityManagerTrait;
    use MultipleSitesRepositoryTrait;

    use ItemsTrait;

    public static function forClass(string|AbstractEntity $entity): static
    {
        return static::readonly()
            ->getBy(['type' => call_user_func([$entity, 'entity_name'])]);;
    }
}