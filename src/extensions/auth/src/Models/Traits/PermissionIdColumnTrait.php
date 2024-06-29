<?php
namespace Autumn\Extensions\Auth\Models\Traits;

use Autumn\Database\Attributes\Column;

trait PermissionIdColumnTrait
{
    #[Column(type: Column::FK, name: 'permission_id', unsigned: true)]
    private int $permissionId = 0;

    /**
     * @return int
     */
    public function getPermissionId(): int
    {
        return $this->permissionId;
    }

    /**
     * @param int $permissionId
     */
    public function setPermissionId(int $permissionId): void
    {
        $this->permissionId = $permissionId;
    }
}