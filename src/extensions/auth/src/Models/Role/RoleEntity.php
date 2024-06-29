<?php
/**
 * Autumn PHP Framework
 *
 * Date:        9/05/2024
 */

namespace Autumn\Extensions\Auth\Models\Role;

use Autumn\Database\Models\RecyclableEntity;
use Autumn\Database\Traits\DescriptionColumnTrait;
use Autumn\Database\Traits\NameColumnTrait;

class RoleEntity extends RecyclableEntity
{
    use NameColumnTrait;
    use DescriptionColumnTrait;

    public const ENTITY_NAME = 'auth_roles';
}