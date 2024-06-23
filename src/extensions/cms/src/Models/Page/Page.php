<?php
/**
 * Autumn PHP Framework
 *
 * Date:        21/06/2024
 */

namespace Autumn\Extensions\Cms\Models\Page;

use Autumn\Database\Interfaces\EntityManagerInterface;

class Page extends PageEntity implements EntityManagerInterface
{

    public static function find(array|int $criteria, array $context = null): ?static
    {
        // TODO: Implement find() method.
    }

    public static function findOrFail(array|int $criteria, array $context = null): static
    {
        // TODO: Implement findOrFail() method.
    }
}