<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Author;

use Autumn\Database\Interfaces\RecyclableRepositoryInterface;
use Autumn\Database\Traits\RecyclableEntityManagerTrait;

class Author extends AuthorEntity implements RecyclableRepositoryInterface
{
    use RecyclableEntityManagerTrait;
}