<?php

namespace Autumn\Extensions\Auth\Models\Authority;

use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Traits\EntityManagerTrait;

class Authority extends AuthorityEntity implements RepositoryInterface
{
    use EntityManagerTrait;

    public function permissions(): RepositoryInterface
    {
        return $this->manyToMany(AuthorityPermission::class);
    }
}