<?php
/**
 * Autumn PHP Framework
 *
 * Date:        12/06/2024
 */

namespace Autumn\Extensions\Cms\Models\Guess;

use Autumn\Database\Interfaces\RecyclableRepositoryInterface;
use Autumn\Database\Traits\RecyclableEntityManagerTrait;
use Autumn\Extensions\Cms\Interfaces\MultipleSiteInterface;
use Autumn\Extensions\Cms\Models\Traits\MultipleSitesRepositoryTrait;

class Guest extends GuestEntity implements MultipleSiteInterface, RecyclableRepositoryInterface
{
    use RecyclableEntityManagerTrait;
    use MultipleSitesRepositoryTrait;

    public function getClientInfo(): ?array
    {
        $config = $this->config();
        if (is_array($config)) {
            return $config;
        }
        return null;
    }

    public function setClientInfo(string|array|null $clientInfo): void
    {
        $this->setConfig($clientInfo);
    }
}