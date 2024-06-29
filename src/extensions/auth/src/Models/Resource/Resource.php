<?php

namespace Autumn\Extensions\Auth\Models\Resource;

use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Traits\EntityManagerTrait;

class Resource extends ResourceEntity implements RepositoryInterface
{
    use EntityManagerTrait;

    public function permissions(): RepositoryInterface
    {
        return $this->manyToMany(ResourcePermission::class);
    }
}