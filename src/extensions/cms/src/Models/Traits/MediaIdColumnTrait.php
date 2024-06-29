<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Traits;

use Autumn\Database\Attributes\Column;

trait MediaIdColumnTrait
{
    #[Column(type: Column::FK, name: 'media_id', unsigned: true)]
    private int $mediaId = 0;


    /**
     * @return int
     */
    public function getMediaId(): int
    {
        return $this->mediaId;
    }

    /**
     * @param int $mediaId
     */
    public function setMediaId(int $mediaId): void
    {
        $this->mediaId = $mediaId;
    }
}