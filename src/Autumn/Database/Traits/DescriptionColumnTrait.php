<?php
namespace Autumn\Database\Traits;

use Autumn\Database\Attributes\Column;

trait DescriptionColumnTrait
{
    #[Column(type: Column::TYPE_TEXT, name: 'description', charset: Column::CHARSET_UTF8MB4)]
    private ?string $description = null;

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}