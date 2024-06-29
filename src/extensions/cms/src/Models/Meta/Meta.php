<?php
/**
 * Autumn PHP Framework
 *
 * Date:        10/05/2024
 */

namespace Autumn\Extensions\Cms\Models\Meta;

use Autumn\Database\Interfaces\RepositoryInterface;

class Meta extends MetaEntity implements RepositoryInterface
{
    use MetaManagerTrait;
}