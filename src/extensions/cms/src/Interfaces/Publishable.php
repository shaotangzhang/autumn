<?php
/**
 * Autumn PHP Framework
 *
 * Date:        10/02/2024
 */

namespace Autumn\Extensions\Cms\Interfaces;

interface Publishable
{
    public function isPublished(): bool;

    public function getPublishedAt(): int;
}