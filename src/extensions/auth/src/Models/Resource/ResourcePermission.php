<?php
namespace Autumn\Extensions\Auth\Models\Resource;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Models\Relation;
use Autumn\Extensions\Auth\Models\Permission\Permission;
use Autumn\Extensions\Auth\Models\Traits\PermissionIdColumnTrait;
use Autumn\Extensions\Auth\Models\Traits\ResourceIdColumnTrait;

class ResourcePermission extends Relation
{
    use ResourceIdColumnTrait;
    use PermissionIdColumnTrait;

    public const ENTITY_NAME = 'auth_resource_permissions';

    public const RELATION_PRIMARY_COLUMN = 'resource_id';
    public const RELATION_SECONDARY_COLUMN = 'permission_id';

    public const RELATION_PRIMARY_CLASS = Resource::class;
    public const RELATION_SECONDARY_CLASS = Permission::class;

    #[Column(type: Column::TYPE_STRING, name: 'action', size: 100, charset: Column::CHARSET_ASCII)]
    private string $action = '';

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }
}