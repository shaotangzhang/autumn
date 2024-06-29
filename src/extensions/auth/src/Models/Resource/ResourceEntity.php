<?php
namespace Autumn\Extensions\Auth\Models\Resource;

use Autumn\Database\Attributes\Index;
use Autumn\Database\Models\Entity;
use Autumn\Database\Traits\DescriptionColumnTrait;
use Autumn\Database\Traits\NameColumnTrait;

#[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE, 'name')]
class ResourceEntity extends Entity
{
    use NameColumnTrait;
    use DescriptionColumnTrait;

    public const ENTITY_NAME = 'auth_resources';
}