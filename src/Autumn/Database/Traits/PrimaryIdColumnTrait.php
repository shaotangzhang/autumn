<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Traits;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;

trait PrimaryIdColumnTrait
{
    #[Column(type: Column::FK, name: self::RELATION_PRIMARY_COLUMN, priority: Column::PRIORITY_FK)]
    private int $primaryId = 0;

    public static function relation_primary_column(): string
    {
        return self::RELATION_PRIMARY_COLUMN;
    }

    public static function relation_primary_class(): string
    {
        return static::RELATION_PRIMARY_CLASS;
    }

    /**
     * @return int
     */
    public function getPrimaryId(): int
    {
        return $this->primaryId;
    }

    /**
     * @param int $primaryId
     */
    public function setPrimaryId(int $primaryId): void
    {
        $this->primaryId = $primaryId;
    }
}