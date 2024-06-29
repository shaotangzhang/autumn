<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Tag;

use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Traits\RecyclableEntityManagerTrait;

class Tag extends TagEntity implements RepositoryInterface
{
    use RecyclableEntityManagerTrait;
}