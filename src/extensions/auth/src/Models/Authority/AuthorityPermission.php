<?php
namespace Autumn\Extensions\Auth\Models\Authority;

use Autumn\Database\Interfaces\RelationRepositoryInterface;
use Autumn\Database\Models\Relation;
use Autumn\Database\Traits\RelationManagerTrait;
use Autumn\Extensions\Auth\Models\Permission\Permission;
use Autumn\Extensions\Auth\Models\Traits\AuthorityIdColumnTrait;
use Autumn\Extensions\Auth\Models\Traits\PermissionIdColumnTrait;

class AuthorityPermission extends Relation implements RelationRepositoryInterface
{
    use AuthorityIdColumnTrait;
    use PermissionIdColumnTrait;
    use RelationManagerTrait;

    public const ENTITY_NAME = 'auth_authority_permissions';

    public const RELATION_PRIMARY_COLUMN = 'authority_id';
    public const RELATION_SECONDARY_COLUMN = 'permission_id';

    public const RELATION_PRIMARY_CLASS = Authority::class;
    public const RELATION_SECONDARY_CLASS = Permission::class;
}