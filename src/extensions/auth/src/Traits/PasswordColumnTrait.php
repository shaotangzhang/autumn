<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Extensions\Auth\Traits;

use Autumn\Database\Attributes\Column;

trait PasswordColumnTrait
{
    #[Column(type: Column::TYPE_STRING, name: 'password', size: 255, charset: Column::CHARSET_ASCII)]
    private string $password = '';

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
}