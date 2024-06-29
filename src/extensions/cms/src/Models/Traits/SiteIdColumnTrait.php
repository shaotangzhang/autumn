<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Traits;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;
use Autumn\Database\Attributes\JsonIgnore;

trait SiteIdColumnTrait
{
    #[JsonIgnore(ignore: true)]
    #[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE)]
    #[Column(type: Column::FK, name: 'site_id', unsigned: true)]
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