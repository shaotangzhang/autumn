<?php
/**
 * Autumn PHP Framework
 *
 * Date:        17/02/2024
 */

namespace Autumn\Extensions\Cms\Models\Option;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;
use Autumn\Database\Models\Entity;
use Autumn\Database\Models\ExtendedEntity;
use Autumn\Database\Traits\ExtendedEntityTrait;
use Autumn\Database\Traits\NameColumnTrait;
use Autumn\Database\Traits\PrimaryIdColumnTrait;
use Autumn\Database\Traits\TypeColumnTrait;
use Autumn\Extensions\Cms\Interfaces\Sortable;
use Autumn\Extensions\Cms\Models\Traits\ConfigColumnTrait;
use Autumn\Extensions\Cms\Models\Traits\SortOrderColumnTrait;

#[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE, self::RELATION_PRIMARY_COLUMN, 'type', 'name')]
class OptionEntity extends Entity implements \Stringable, Sortable
{
    use PrimaryIdColumnTrait;

    use TypeColumnTrait;
    use NameColumnTrait;
    use SortOrderColumnTrait;

    public const ENTITY_NAME = 'cms_options';
    public const DEFAULT_TYPE = 'default';

    public const RELATION_PRIMARY_COLUMN = 'primary_id';
    public const RELATION_PRIMARY_CLASS = null;

    #[Column(type: Column::TYPE_STRING, name: 'value', size: 200, charset: Column::CHARSET_UTF8)]
    private ?string $value = null;

    public function __toString(): string
    {
        return $this->value ?? '';
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     */
    public function setValue(?string $value): void
    {
        $this->value = $value;
    }
}