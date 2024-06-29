<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Traits;

use Autumn\Database\Attributes\Column;

trait VisitCountColumnTrait
{
    #[Column(type: Column::TYPE_INT, name: 'visit_count', unsigned: true)]
    private int $visitCount = 0;

    /**
     * @return int
     */
    public function getVisitCount(): int
    {
        return $this->visitCount;
    }

    /**
     * @param int $visitCount
     */
    public function setVisitCount(int $visitCount): void
    {
        $this->visitCount = $visitCount;
    }
}