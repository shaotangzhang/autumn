<?php
/**
 * Autumn PHP Framework
 *
 * Date:        17/02/2024
 */

namespace Autumn\Extensions\Cms\Models\Option;

use Autumn\Database\Attributes\Index;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Traits\EntityManagerTrait;
use Autumn\Extensions\Cms\Interfaces\MultipleLangInterface;
use Autumn\Extensions\Cms\Interfaces\MultipleSiteInterface;
use Autumn\Extensions\Cms\Models\Traits\LangColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\MultipleSitesRepositoryTrait;

#[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE, 'site_id', 'type', 'primary_id', 'name', 'value', 'lang')]
class Option extends OptionEntity implements RepositoryInterface, MultipleSiteInterface, MultipleLangInterface
{
    use EntityManagerTrait;
    use LangColumnTrait;
    use MultipleSitesRepositoryTrait;
}