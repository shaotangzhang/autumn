<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/14
 */

namespace App\Services\Traits;

trait GlobalServiceTrait
{
    private int $siteId = 0;

    /**
     * @return int
     */
    public function getSiteId(): int
    {
        return $this->siteId;
    }

    /**
     * @param int $siteId
     */
    public function setSiteId(int $siteId): void
    {
        $this->siteId = $siteId;
    }
}