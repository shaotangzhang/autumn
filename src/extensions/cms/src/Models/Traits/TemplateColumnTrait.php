<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Traits;

use Autumn\Database\Attributes\Column;

trait TemplateColumnTrait
{

    #[Column(type: Column::TYPE_STRING, name: 'template', size: 50, charset: Column::CHARSET_ASCII)]
    private ?string $template = null;

    /**
     * @return string|null
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @param string|null $template
     */
    public function setTemplate(?string $template): void
    {
        $this->template = $template;
    }
}