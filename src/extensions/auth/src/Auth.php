<?php

namespace Autumn\Extensions\Auth;

use Autumn\Extensions\Auth\Models\Authority\Authority;
use Autumn\Extensions\Auth\Models\Authority\AuthorityPermission;
use Autumn\Extensions\Auth\Models\Permission\Permission;
use Autumn\Extensions\Auth\Models\Resource\Resource;
use Autumn\Extensions\Auth\Models\Resource\ResourcePermission;
use Autumn\Extensions\Auth\Models\Role\Role;
use Autumn\Extensions\Auth\Models\Role\RolePermission;
use Autumn\Extensions\Auth\Models\User\User;
use Autumn\Extensions\Auth\Models\User\UserRole;
use Autumn\System\Extension;

class Auth extends Extension
{
    public const REGISTERED_ENTITIES = [
        Authority::class,
        AuthorityPermission::class,

        Permission::class,

        Resource::class,
        ResourcePermission::class,

        Role::class,
        RolePermission::class,

        User::class,
        UserRole::class,
    ];
}