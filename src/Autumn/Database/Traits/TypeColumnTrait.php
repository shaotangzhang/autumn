<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Traits;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;

trait TypeColumnTrait
{
    #[Index(Index::DEFAULT_INDEX_NAME)]
    #[Column(type: Column::TYPE_STRING, name: 'type', size: 40, charset: Column::CHARSET_ASCII)]
    private string $type = '';

    public static function defaultType(): string
    {
        return static::DEFAULT_TYPE ?? '';
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type ?: static::defaultType();
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }
}