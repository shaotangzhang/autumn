<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Traits;

use Autumn\Database\Attributes\Column;

trait CollectionIdColumnTrait
{
    #[Column(type: Column::FK, name: 'collection_id', unsigned: true)]
    private int $collectionId = 0;

    /**
     * @return int
     */
    public function getCollectionId(): int
    {
        return $this->collectionId;
    }

    /**
     * @param int $collectionId
     */
    public function setCollectionId(int $collectionId): void
    {
        $this->collectionId = $collectionId;
    }
}