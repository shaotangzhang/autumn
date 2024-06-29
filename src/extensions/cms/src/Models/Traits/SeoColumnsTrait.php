<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/01/2024
 */

namespace Autumn\Extensions\Cms\Models\Traits;

use Autumn\Database\Attributes\Column;

trait SeoColumnsTrait
{
    #[Column(type: Column::TYPE_TEXT, name: 'page_title')]
    private ?string $pageTitle = null;

    #[Column(type: Column::TYPE_TEXT, name: 'page_description')]
    private ?string $pageDescription = null;

    #[Column(type: Column::TYPE_TEXT, name: 'page_keywords')]
    private ?string $pageKeywords = null;

    #[Column(type: Column::TYPE_TEXT, name: 'page_canonical')]
    private ?string $pageCanonical = null;

    /**
     * @return string|null
     */
    public function getPageTitle(): ?string
    {
        return $this->pageTitle;
    }

    /**
     * @param string|null $pageTitle
     */
    public function setPageTitle(?string $pageTitle): void
    {
        $this->pageTitle = $pageTitle;
    }

    /**
     * @return string|null
     */
    public function getPageDescription(): ?string
    {
        return $this->pageDescription;
    }

    /**
     * @param string|null $pageDescription
     */
    public function setPageDescription(?string $pageDescription): void
    {
        $this->pageDescription = $pageDescription;
    }

    /**
     * @return string|null
     */
    public function getPageKeywords(): ?string
    {
        return $this->pageKeywords;
    }

    /**
     * @param string|null $pageKeywords
     */
    public function setPageKeywords(?string $pageKeywords): void
    {
        $this->pageKeywords = $pageKeywords;
    }

    /**
     * @return string|null
     */
    public function getPageCanonical(): ?string
    {
        return $this->pageCanonical;
    }

    /**
     * @param string|null $pageCanonical
     */
    public function setPageCanonical(?string $pageCanonical): void
    {
        $this->pageCanonical = $pageCanonical;
    }
}