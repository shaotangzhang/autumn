<?php
/**
 * Autumn PHP Framework
 *
 * Date:        12/06/2024
 */

namespace Autumn\Extensions\Cms\Interfaces;

use Autumn\Database\Interfaces\RepositoryInterface;

interface MetaSupportedInterface
{
    public function meta(string $lang = null): RepositoryInterface;

    public function metadata(string $name, string $lang = null): ?string;
}