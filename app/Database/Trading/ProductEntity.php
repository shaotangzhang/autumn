<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/8
 */

namespace App\Database\Trading;

use App\Database\Blog\AbstractArticle;
use Autumn\Database\Attributes\Column;
use Autumn\Database\Attributes\Index;
use Autumn\Database\RecyclableEntity;

class ProductEntity extends AbstractArticle
{
    public const ENTITY_NAME = 'trading_products';

    #[Column(type: 'bigint')]
    #[Index(index: true, unique: true)]
    #[Index('i_slug')]
    private int $siteId = 0;


    #[Index('i_identity')]
    #[Column(size: 20, collation: 'ascii_general_ci')]
    private string $model = '';

    #[Index('i_identity')]
    #[Column(size: 20, collation: 'ascii_general_ci')]
    private string $mpn = '';

    #[Index('i_identity')]
    #[Column(size: 50, collation: 'ascii_general_ci')]
    private string $brand = '';

    #[Index('i_identity')]
    #[Index(index: true, unique: true)]
    #[Column(size: 50, collation: 'ascii_general_ci')]
    private string $sku = '';

    #[Index('i_identity')]
    #[Column(type: 'char', size: 12, collation: 'ascii_general_ci')]
    private string $upc = '';

    #[Index('i_identity')]
    #[Column(type: 'char', size: 13, collation: 'ascii_general_ci')]
    private string $isbn = '';

    #[Index('i_identity')]
    #[Column(type: 'char', size: 13, collation: 'ascii_general_ci')]
    private string $ean = '';

    #[Index('i_identity')]
    #[Column(type: 'char', size: 10, collation: 'ascii_general_ci')]
    private string $color = '';

    #[Index('i_identity')]
    #[Column(type: 'char', size: 10, collation: 'ascii_general_ci')]
    private string $size = '';

    #[Column(type: 'char', size: 10, collation: 'ascii_general_ci')]
    private string $packageType = '';

    #[Column(type: 'char', size: 5, collation: 'ascii_general_ci')]
    private string $stockArea = '';

    #[Column(type: 'char', size: 5, collation: 'ascii_general_ci')]
    private string $stockAisle= '';

    #[Column(type: 'char', size: 5, collation: 'ascii_general_ci')]
    private string $stockBay = '';

    #[Column(type: 'char', size: 10, collation: 'ascii_general_ci')]
    private string $customOrigin = '';

    #[Column(type: 'char', size: 10, collation: 'ascii_general_ci')]
    private string $customCode = ''; // HS code

    private float $customValue = 0.00;

    private int $customWeight = 0;

    #[Index('i_pricing')]
    private float $cost = 0.00;

    #[Index('i_pricing')]
    private float $price = 0.00;

    #[Index('i_pricing')]
    private float $rrp = 0.00;

    #[Index('i_pricing')]
    #[Index('i_package')]
    private int $quantity = 0;

    #[Index('i_measurement')]
    private int $weight = 0;

    #[Index('i_measurement')]
    private int $length = 0;

    #[Index('i_measurement')]
    private int $width = 0;

    #[Index('i_measurement')]
    private int $height = 0;

    #[Index('i_measurement')]
    private int $grossWeight = 0;

    #[Index('i_measurement')]
    private int $grossLength = 0;

    #[Index('i_measurement')]
    private int $grossWidth = 0;

    #[Index('i_measurement')]
    private int $grossHeight = 0;

    #[Index('i_package')]
    private int $lots = 0;

    #[Index('i_package')]
    private bool $dangerous = false;

    #[Index('i_package')]
    private bool $fragile = false;

    #[Index('i_pricing')]
    private bool $digital = false;

    #[Index('i_pricing')]
    private bool $service = false;

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
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * @param string $model
     */
    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    /**
     * @return string
     */
    public function getMpn(): string
    {
        return $this->mpn;
    }

    /**
     * @param string $mpn
     */
    public function setMpn(string $mpn): void
    {
        $this->mpn = $mpn;
    }

    /**
     * @return string
     */
    public function getBrand(): string
    {
        return $this->brand;
    }

    /**
     * @param string $brand
     */
    public function setBrand(string $brand): void
    {
        $this->brand = $brand;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     */
    public function setSku(string $sku): void
    {
        $this->sku = $sku;
    }

    /**
     * @return string
     */
    public function getUpc(): string
    {
        return $this->upc;
    }

    /**
     * @param string $upc
     */
    public function setUpc(string $upc): void
    {
        $this->upc = $upc;
    }

    /**
     * @return string
     */
    public function getIsbn(): string
    {
        return $this->isbn;
    }

    /**
     * @param string $isbn
     */
    public function setIsbn(string $isbn): void
    {
        $this->isbn = $isbn;
    }

    /**
     * @return string
     */
    public function getEan(): string
    {
        return $this->ean;
    }

    /**
     * @param string $ean
     */
    public function setEan(string $ean): void
    {
        $this->ean = $ean;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getSize(): string
    {
        return $this->size;
    }

    /**
     * @param string $size
     */
    public function setSize(string $size): void
    {
        $this->size = $size;
    }

    /**
     * @return string
     */
    public function getPackageType(): string
    {
        return $this->packageType;
    }

    /**
     * @param string $packageType
     */
    public function setPackageType(string $packageType): void
    {
        $this->packageType = $packageType;
    }

    /**
     * @return string
     */
    public function getStockArea(): string
    {
        return $this->stockArea;
    }

    /**
     * @param string $stockArea
     */
    public function setStockArea(string $stockArea): void
    {
        $this->stockArea = $stockArea;
    }

    /**
     * @return string
     */
    public function getStockBay(): string
    {
        return $this->stockBay;
    }

    /**
     * @param string $stockBay
     */
    public function setStockBay(string $stockBay): void
    {
        $this->stockBay = $stockBay;
    }

    /**
     * @return string
     */
    public function getCustomOrigin(): string
    {
        return $this->customOrigin;
    }

    /**
     * @param string $customOrigin
     */
    public function setCustomOrigin(string $customOrigin): void
    {
        $this->customOrigin = $customOrigin;
    }

    /**
     * @return string
     */
    public function getCustomCode(): string
    {
        return $this->customCode;
    }

    /**
     * @param string $customCode
     */
    public function setCustomCode(string $customCode): void
    {
        $this->customCode = $customCode;
    }

    /**
     * @return float
     */
    public function getCustomValue(): float
    {
        return $this->customValue;
    }

    /**
     * @param float $customValue
     */
    public function setCustomValue(float $customValue): void
    {
        $this->customValue = $customValue;
    }

    /**
     * @return int
     */
    public function getCustomWeight(): int
    {
        return $this->customWeight;
    }

    /**
     * @param int $customWeight
     */
    public function setCustomWeight(int $customWeight): void
    {
        $this->customWeight = $customWeight;
    }

    /**
     * @return float
     */
    public function getCost(): float
    {
        return $this->cost;
    }

    /**
     * @param float $cost
     */
    public function setCost(float $cost): void
    {
        $this->cost = $cost;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getRrp(): float
    {
        return $this->rrp;
    }

    /**
     * @param float $rrp
     */
    public function setRrp(float $rrp): void
    {
        $this->rrp = $rrp;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     */
    public function setWeight(int $weight): void
    {
        $this->weight = $weight;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @param int $length
     */
    public function setLength(int $length): void
    {
        $this->length = $length;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     */
    public function setWidth(int $width): void
    {
        $this->width = $width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    public function setHeight(int $height): void
    {
        $this->height = $height;
    }

    /**
     * @return int
     */
    public function getGrossWeight(): int
    {
        return $this->grossWeight;
    }

    /**
     * @param int $grossWeight
     */
    public function setGrossWeight(int $grossWeight): void
    {
        $this->grossWeight = $grossWeight;
    }

    /**
     * @return int
     */
    public function getGrossLength(): int
    {
        return $this->grossLength;
    }

    /**
     * @param int $grossLength
     */
    public function setGrossLength(int $grossLength): void
    {
        $this->grossLength = $grossLength;
    }

    /**
     * @return int
     */
    public function getGrossWidth(): int
    {
        return $this->grossWidth;
    }

    /**
     * @param int $grossWidth
     */
    public function setGrossWidth(int $grossWidth): void
    {
        $this->grossWidth = $grossWidth;
    }

    /**
     * @return int
     */
    public function getGrossHeight(): int
    {
        return $this->grossHeight;
    }

    /**
     * @param int $grossHeight
     */
    public function setGrossHeight(int $grossHeight): void
    {
        $this->grossHeight = $grossHeight;
    }

    /**
     * @return int
     */
    public function getLots(): int
    {
        return $this->lots;
    }

    /**
     * @param int $lots
     */
    public function setLots(int $lots): void
    {
        $this->lots = $lots;
    }

    /**
     * @return bool
     */
    public function isDangerous(): bool
    {
        return $this->dangerous;
    }

    /**
     * @param bool $dangerous
     */
    public function setDangerous(bool $dangerous): void
    {
        $this->dangerous = $dangerous;
    }

    /**
     * @return bool
     */
    public function isFragile(): bool
    {
        return $this->fragile;
    }

    /**
     * @param bool $fragile
     */
    public function setFragile(bool $fragile): void
    {
        $this->fragile = $fragile;
    }

    /**
     * @return bool
     */
    public function isDigital(): bool
    {
        return $this->digital;
    }

    /**
     * @param bool $digital
     */
    public function setDigital(bool $digital): void
    {
        $this->digital = $digital;
    }

    /**
     * @return bool
     */
    public function isService(): bool
    {
        return $this->service;
    }

    /**
     * @param bool $service
     */
    public function setService(bool $service): void
    {
        $this->service = $service;
    }

    /**
     * @return string
     */
    public function getStockAisle(): string
    {
        return $this->stockAisle;
    }

    /**
     * @param string $stockAisle
     */
    public function setStockAisle(string $stockAisle): void
    {
        $this->stockAisle = $stockAisle;
    }


}