<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Traits;

use Autumn\Database\Attributes\Column;

trait SecondaryIdColumnTrait
{
    #[Column(type: Column::FK, name: self::ENTITY_SECONDARY_COLUMN, unsigned: true)]
    private int $secondaryId = 0;

    /**
     * @return int
     */
    public function getSecondaryId(): int
    {
        return $this->secondaryId;
    }

    /**
     * @param int $secondaryId
     */
    public function setSecondaryId(int $secondaryId): void
    {
        $this->secondaryId = $secondaryId;
    }
}