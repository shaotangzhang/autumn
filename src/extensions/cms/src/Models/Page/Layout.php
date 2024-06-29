<?php
/**
 * Autumn PHP Framework
 *
 * Date:        27/06/2024
 */

namespace Autumn\Extensions\Cms\Models\Page;

class Layout extends Page
{
    public const DEFAULT_TYPE = 'layouts';

    public static function fromPageEntity(PageEntity $entity): static
    {
        // optimize later.
        return static::from($entity->toArray());
    }
}