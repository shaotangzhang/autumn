<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Traits;

use Autumn\Database\Attributes\Column;

trait TagIdColumnTrait
{
    #[Column(type: Column::FK, name: 'tag_id', unsigned: true)]
    private int $tagId = 0;


    /**
     * @return int
     */
    public function getTagId(): int
    {
        return $this->tagId;
    }

    /**
     * @param int $tagId
     */
    public function setTagId(int $tagId): void
    {
        $this->tagId = $tagId;
    }
}