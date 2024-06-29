<?php
namespace Autumn\Extensions\Auth\Models\Traits;

use Autumn\Database\Attributes\Column;

trait RoleIdColumnTrait
{
    #[Column(type: Column::FK, name: 'role_id', unsigned: true)]
    private int $roleId = 0;

    /**
     * @return int
     */
    public function getRoleId(): int
    {
        return $this->roleId;
    }

    /**
     * @param int $roleId
     */
    public function setRoleId(int $roleId): void
    {
        $this->roleId = $roleId;
    }
}