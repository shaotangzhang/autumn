<?php

namespace Autumn\Extensions\Auth\Models\User;

use Autumn\Database\Interfaces\RecyclableRepositoryInterface;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Traits\RecyclableEntityManagerTrait;

class User extends UserEntity implements RecyclableRepositoryInterface
{
    use RecyclableEntityManagerTrait;

    public function roles(): RepositoryInterface
    {
        return $this->manyToMany(UserRole::class);
    }
}