<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/8
 */

namespace App\Database\Trading;

use Autumn\Database\AbstractEntity;
use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;

class ProductMetaEntity extends AbstractEntity
{
    public const ENTITY_NAME = 'trading_product_meta';

    #[Index(index: true, unique: true)]
    #[Index]
    #[Column(type: 'bigint')]
    private int $productId = 0;

    #[Index(index: true, unique: true)]
    #[Index]
    #[Column(type: 'bigint')]
    private int $termId = 0;

    #[Index]
    private int $sortOrder = 0;

    #[Index(index: true, unique: true)]
    #[Column(size: 20, collation: 'ascii_general_ci')]
    private string $type = '';

    #[Index]
    #[Column]
    private string $value = '';

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     */
    public function setProductId(int $productId): void
    {
        $this->productId = $productId;
    }

    /**
     * @return int
     */
    public function getTermId(): int
    {
        return $this->termId;
    }

    /**
     * @param int $termId
     */
    public function setTermId(int $termId): void
    {
        $this->termId = $termId;
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     */
    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
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
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}