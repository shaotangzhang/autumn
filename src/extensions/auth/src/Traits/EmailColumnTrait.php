<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Extensions\Auth\Traits;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;

trait EmailColumnTrait
{
    #[Index(Index::DEFAULT_UNIQUE_NAME, Index::UNIQUE)]
    #[Column(type: Column::TYPE_STRING, name: 'email', size: 255, charset: Column::CHARSET_ASCII)]
    private string $email = '';

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}