<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Traits;

use Autumn\Database\Attributes\Column;

trait PhoneNumberColumnTrait
{
    #[Column(type: Column::TYPE_STRING, name: 'phone_number', size: 200, charset: Column::CHARSET_UTF8MB4)]
    private string $phoneNUmber = '';

    /**
     * @return string
     */
    public function getPhoneNumber(): string
    {
        return $this->phoneNUmber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->phoneNUmber = $phoneNumber;
    }
}