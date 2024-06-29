<?php

namespace Autumn\Extensions\Auth\Models\Traits;

use Autumn\Database\Attributes\Column;

trait IPv4ColumnTrait
{
    #[Column(type: Column::TYPE_STRING, name: 'ip', size: 15, charset: Column::CHARSET_ASCII)]
    private string $ip = '';

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }
}