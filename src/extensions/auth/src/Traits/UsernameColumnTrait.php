<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Extensions\Auth\Traits;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;

trait UsernameColumnTrait
{
    #[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE)]
    #[Column(type: Column::TYPE_STRING, name:'username', size: 40, charset: Column::CHARSET_ASCII)]
    private string $username = '';

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }
}