<?php
namespace Autumn\Extensions\Auth\Models\Role;

use Autumn\Database\Models\Relation;
use Autumn\Extensions\Auth\Models\Permission\Permission;
use Autumn\Extensions\Auth\Models\Traits\PermissionIdColumnTrait;
use Autumn\Extensions\Auth\Models\Traits\RoleIdColumnTrait;

class RolePermission extends Relation
{
    use RoleIdColumnTrait;
    use PermissionIdColumnTrait;

    public const ENTITY_NAME = 'auth_role_permissions';
    public const RELATION_PRIMARY_COLUMN = 'role_id';
    public const RELATION_SECONDARY_COLUMN = 'permission_id';
    public const RELATION_PRIMARY_CLASS = Role::class;
    public const RELATION_SECONDARY_CLASS = Permission::class;
}