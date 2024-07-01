<?php

namespace Autumn\Extensions\Auth\Models\Role;

use Autumn\Database\Interfaces\RecyclableRepositoryInterface;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Traits\RecyclableEntityManagerTrait;
use Autumn\Extensions\Auth\Models\User\UserRole;

class Role extends RoleEntity implements RecyclableRepositoryInterface
{
    use RecyclableEntityManagerTrait;

    public const USER = 'default';
    public const ADMIN = 'admin';
    public const DEVELOPER = 'developer';
    public const SUPERVISOR = 'supervisor';

    public static function supervisor(): static
    {
        static $supervisor;

        return $supervisor ??= static::findOrNew(['name' => 'supervisor']);
    }

    public function users(): RepositoryInterface
    {
        return $this->manyToMany(UserRole::class);
    }

    public function permissions(): RepositoryInterface
    {
        return $this->manyToMany(RolePermission::class);
    }
}