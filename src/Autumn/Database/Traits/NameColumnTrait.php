<?php

namespace Autumn\Database\Traits;

use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;

trait NameColumnTrait
{
    #[Column(type: Column::TYPE_STRING, name: 'name', size: 100, charset: Column::CHARSET_ASCII)]
    private string $name = '';

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
}