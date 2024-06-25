<?php
/**
 * Autumn PHP Framework
 *
 * Date:        10/05/2024
 */

namespace Autumn\Database\Traits;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;

trait RelationTrait
{
    use PrimaryIdColumnTrait;

    #[Column(type: Column::FK, name: self::RELATION_SECONDARY_COLUMN, priority: Column::PRIORITY_FK)]
    private int $secondaryId = 0;

    public static function relation_secondary_column(): string
    {
        return self::RELATION_SECONDARY_COLUMN;
    }

    public static function relation_secondary_class(): string
    {
        return static::RELATION_SECONDARY_CLASS ?? '';
    }

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