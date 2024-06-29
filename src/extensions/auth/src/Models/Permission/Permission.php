<?php
namespace Autumn\Extensions\Auth\Models\Permission;

use Autumn\Database\Interfaces\RecyclableRepositoryInterface;
use Autumn\Database\Traits\RecyclableEntityManagerTrait;

class Permission extends PermissionEntity implements RecyclableRepositoryInterface
{
    use RecyclableEntityManagerTrait;
}