<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Traits;

use Autumn\Database\Attributes\Column;

trait EmailColumnTrait
{
    #[Column(type: Column::TYPE_STRING, name: 'email', size: 255, charset: Column::CHARSET_UTF8)]
    private ?string $email = null;

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email ?? '';
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }
}