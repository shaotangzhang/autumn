<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Meta;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;
use Autumn\Database\Models\Entity;
use Autumn\Database\Models\ExtendedEntity;
use Autumn\Database\Traits\ExtendedEntityTrait;
use Autumn\Extensions\Cms\Models\Traits\LangColumnTrait;

#[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE, 'name', 'lang', 'code')]
class MetaEntity extends Entity
{
    use LangColumnTrait;

    public const ENTITY_NAME = 'cms_meta';

    public const COLUMN_PRIMARY_KEY = null;

    public const DEFAULT_VALUE = null;

    public const COLUMN_NAME_SIZE = 255;

    public const COLUMN_CODE_SIZE = 170;

    #[Column(type: Column::TYPE_STRING, name: 'name', size: self::COLUMN_NAME_SIZE, charset: Column::CHARSET_ASCII)]
    private string $name = '';

    #[Column(type: Column::TYPE_TEXT, name: 'value', charset: Column::CHARSET_UTF8)]
    private ?string $value = self::DEFAULT_VALUE;

    #[Column(type: Column::TYPE_STRING, name: 'code', size: self::COLUMN_CODE_SIZE, charset: Column::CHARSET_UTF8)]
    private ?string $code = self::DEFAULT_VALUE;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
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