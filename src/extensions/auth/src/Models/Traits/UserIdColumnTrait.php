<?php

namespace Autumn\Extensions\Auth\Models\Traits;

use Autumn\Database\Attributes\Column;

trait UserIdColumnTrait
{
    #[Column(type: Column::FK, name: 'user_id', unsigned: true)]
    private int $userId = 0;

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }
}