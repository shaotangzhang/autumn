<?php
/**
 * Autumn PHP Framework
 *
 * Date:        11/05/2024
 */

namespace Autumn\Extensions\Cms\Models\Traits;

use Autumn\Lang\Number;

trait MultipleSitesRepositoryTrait
{
    use SiteIdColumnTrait;

    public function ofSite(int $siteId = null): static
    {
        if ($siteId = $siteId ?? Number::int(env('SITE_ID'))) {
            $this->setSiteId($siteId);

            if ($alias = $this->aliasName()) {
                $column = "$alias.site_id";
            } else {
                $column = 'site_id';
            }

            $this->where($column, $siteId);
        }

        return $this;
    }
}