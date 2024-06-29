<?php
namespace Autumn\Extensions\Auth\Models\User;

use Autumn\Database\Interfaces\RelationInterface;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Models\Relation;
use Autumn\Database\Traits\RelationManagerTrait;
use Autumn\Extensions\Auth\Models\Role\Role;
use Autumn\Extensions\Auth\Models\Traits\RoleIdColumnTrait;
use Autumn\Extensions\Auth\Models\Traits\UserIdColumnTrait;

class UserRole extends Relation implements RepositoryInterface, RelationInterface
{
    use UserIdColumnTrait;
    use RoleIdColumnTrait;
    use RelationManagerTrait;

    public const ENTITY_NAME = 'auth_user_roles';
    public const RELATION_PRIMARY_COLUMN = 'user_id';
    public const RELATION_SECONDARY_COLUMN = 'role_id';

    public const RELATION_PRIMARY_CLASS = User::class;
    public const RELATION_SECONDARY_CLASS = Role::class;
}