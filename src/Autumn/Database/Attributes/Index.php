<?php
/**
 * Autumn PHP Framework
 *
 * Date:        18/02/2024
 */

namespace Autumn\Database\Attributes;

use Attribute;
use Autumn\Attributes\HasOfReflection;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Index
{
    use HasOfReflection;

    public const INDEX = 'KEY';
    public const UNIQUE = 'UNIQUE KEY';
    public const FULLTEXT = 'FULLTEXT KEY';

    public const DEFAULT_INDEX_NAME = 'idx_default';
    public const DEFAULT_UNIQUE_NAME = 'udx_default';
    public const DEFAULT_FULLTEXT_NAME = 'fdx_default';

    private array $columns;

    public function __construct(private string $name, private string $type = self::INDEX, string ...$columns)
    {
        $this->columns = $columns;
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
     * @return string[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param array $columns
     */
    public function setColumns(string ...$columns): void
    {
        $this->columns = $columns;
    }
}