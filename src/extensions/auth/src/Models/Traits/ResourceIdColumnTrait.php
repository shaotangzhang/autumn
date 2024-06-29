<?php
namespace Autumn\Extensions\Auth\Models\Traits;

use Autumn\Database\Attributes\Column;

trait ResourceIdColumnTrait
{
    #[Column(type: Column::FK, name: 'resource_id', unsigned: true)]
    private int $resourceId = 0;

    /**
     * @return int
     */
    public function getResourceId(): int
    {
        return $this->resourceId;
    }

    /**
     * @param int $resourceId
     */
    public function setResourceId(int $resourceId): void
    {
        $this->resourceId = $resourceId;
    }
}