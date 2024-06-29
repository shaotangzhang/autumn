<?php
namespace Autumn\Extensions\Auth\Models\Authority;

use Autumn\Database\Attributes\Index;
use Autumn\Database\Models\Entity;
use Autumn\Database\Traits\DescriptionColumnTrait;
use Autumn\Database\Traits\NameColumnTrait;

#[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE, 'name')]
abstract class AuthorityEntity extends Entity implements \Stringable
{
    use NameColumnTrait;
    use DescriptionColumnTrait;

    public const ENTITY_NAME = 'auth_authorities';

    public function __toString(): string
    {
        return $this->name;
    }
}