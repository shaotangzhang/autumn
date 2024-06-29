<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Interfaces;

interface Sortable
{
    public function getSortOrder(): int;
}