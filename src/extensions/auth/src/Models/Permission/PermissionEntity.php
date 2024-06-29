<?php
namespace Autumn\Extensions\Auth\Models\Permission;

use Autumn\Database\Models\RecyclableEntity;
use Autumn\Database\Traits\DescriptionColumnTrait;
use Autumn\Database\Traits\NameColumnTrait;

class PermissionEntity extends RecyclableEntity implements \Stringable
{
    use NameColumnTrait;
    use DescriptionColumnTrait;

    public const ENTITY_NAME = 'auth_permissions';
    
    public function __toString(): string
    {
        return $this->name;
    }
}