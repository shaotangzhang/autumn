<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/05/2024
 */

namespace Autumn\Extensions\Cms\Interfaces;

interface MultipleSiteInterface
{
    public function getSiteId(): int;

    public function ofSite(): static;
}