<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/11
 */

namespace App\Database\Blog;

use Autumn\Database\AbstractEntity;
use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;

class TermEntity extends AbstractEntity
{
    public const ENTITY_NAME = 'blog_terms';
    public const DEFAULT_TYPE = 'standard';

    #[Index(index: true, unique: true)]
    #[Column(type: 'bigint')]
    private int $siteId = 0;

    #[Index(index: true, unique: true)]
    #[Column(type: 'bigint')]
    private int $parentId = 0;

    #[Index(index: true, unique: true)]
    #[Column(size: 100, collation: 'ascii_general_ci')]
    private string $name = '';

    #[Column(type: 'text')]
    private ?string $description = null;

    #[Index(index: true, unique: true)]
    #[Column(type: 'char', size: 10, collation: 'ascii_general_ci')]
    private string $type = self::DEFAULT_TYPE;

    #[Index]
    private int $value = 0;

    /**
     * @return int
     */
    public function getSiteId(): int
    {
        return $this->siteId;
    }

    /**
     * @param int $siteId
     */
    public function setSiteId(int $siteId): void
    {
        $this->siteId = $siteId;
    }

    /**
     * @return int
     */
    public function getParentId(): int
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId
     */
    public function setParentId(int $parentId): void
    {
        $this->parentId = $parentId;
    }

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

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @param int $value
     */
    public function setValue(int $value): void
    {
        $this->value = $value;
    }
}