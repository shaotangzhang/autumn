<?php
/**
 * Autumn PHP Framework
 *
 * Date:        12/06/2024
 */

namespace Autumn\Extensions\Cms\Models\Message;

use Autumn\Database\Interfaces\RecyclableRepositoryInterface;
use Autumn\Database\Traits\RecyclableEntityManagerTrait;
use Autumn\Extensions\Cms\Interfaces\MultipleSiteInterface;
use Autumn\Extensions\Cms\Models\Traits\MultipleSitesRepositoryTrait;

class Message extends MessageEntity implements MultipleSiteInterface, RecyclableRepositoryInterface
{
    use RecyclableEntityManagerTrait;
    use MultipleSitesRepositoryTrait;
}