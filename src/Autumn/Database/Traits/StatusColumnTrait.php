<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database\Traits;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;

trait StatusColumnTrait
{
    #[Column(type: Column::TYPE_STRING, name: 'status', size: 20, charset: Column::CHARSET_ASCII)]
    private string $status = '';

    public static function defaultStatus(): string
    {
        return static::DEFAULT_STATUS;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}