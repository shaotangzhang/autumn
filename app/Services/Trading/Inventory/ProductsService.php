<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/8
 */

namespace App\Services\Trading\Inventory;

use App\Models\Blog\Category;
use App\Models\Blog\Media;
use App\Models\Trading\Products\Product;
use App\Models\Trading\Products\ProductCategories;
use App\Models\Trading\Products\ProductMedia;
use App\Services\AbstractService;
use Autumn\Database\Interfaces\QueryBuilderInterface;
use Autumn\Database\QueryBuilder\Builder;
use Autumn\Http\Exceptions\ConflictException;
use Autumn\Http\Exceptions\NotAcceptableException;
use Autumn\Http\Exceptions\NotFoundException;
use Autumn\System\Attributes\Service;
use Exception;
use RuntimeException;

#[Service]
class ProductsService extends AbstractService
{
    public const CATEGORY_TYPE = 'product';

    private string $categoryType = self::CATEGORY_TYPE;

    /**
     * @return string
     */
    public function getCategoryType(): string
    {
        return $this->categoryType;
    }

    /**
     * @param string $categoryType
     */
    public function setCategoryType(string $categoryType): void
    {
        $this->categoryType = $categoryType;
    }

    public function findCategory(int $categoryId): ?Category
    {
        return $this->getCategoriesService()
            ->findCategory($categoryId, $this->getCategoryType());
    }

    /**
     * @throws NotFoundException
     */
    public function findCategoryOrFail(int $categoryId): Category
    {
        return $this->getCategoriesService()
            ->findCategoryOrFail($categoryId, $this->getCategoryType());
    }

    /**
     * @param int|null $limit
     * @param int|null $page
     * @return Builder|null
     */
    public function createQuery(?int $limit, ?int $page): ?QueryBuilderInterface
    {
        return Product::withoutTrashed()
            ->with('siteId', $this->getSiteId())
            ->limit($limit, $page)->callback(static::class);
    }

    public function findProduct(int|string|array $product, int $parentId = null, string $type = null): ?Product
    {
        Product::withoutTrashed()
            ->with('siteId', $this->getSiteId())
            ->when(is_int($product), 'id', $product)
            ->when(is_string($product), 'sku', $product)
            ->when(is_array($product), $product)
            ->when($type, 'type', $type)
            ->when(is_int($parentId), 'parentId', $parentId);

        return Product::find();
    }

    /**
     * @throws NotFoundException
     */
    public function findProductOrFail(int|string|array $product, int $parentId = null, string $type = null): ?Product
    {
        if ($result = $this->findProduct($product, $parentId, $type)) {
            return $result;
        }

        throw new NotFoundException('Product is not found.');
    }

    public function createProduct(Product|array $item): Product
    {
        if (is_array($item)) {
            $item = Product::from($item);
        }

        if (!$item->isNew()) {
            throw new RuntimeException('The given data is not a new record.');
        }

        if ($item->save()) {
            return $item;
        }

        throw new ConflictException('Not created.');
    }

    /**
     * @throws NotFoundException
     */
    public function updateProduct(int|Product $item, array $changes = null): ?int
    {
        if (is_int($item)) {
            $item = $this->findProductOrFail($item);
        }

        if ($item->isNew()) {
            throw new NotFoundException('Item is not found.');
        }

        return $item->save($changes);
    }

    public function updateProductImage(Product $item, ?int $imageId = null): string
    {
        if ($imageId) {
            if ($image = ProductMedia::of($item->getId(), $imageId, 'image')->secondary()['link'] ?? null) {
                if ($item->save(['image' => $image])) {
                    return $image;
                }
            }
        } elseif ($image = $item->through(ProductMedia::class)->hasAny('image')['link'] ?? null) {
            if ($item->save(['image' => $image])) {
                return $image;
            }
        }

        return '';
    }

    public function loadProductImages(?Product $item): void
    {
        $item->loadMediaFrom(ProductMedia::make($item->getId(), null, 'image')->hasMany());
    }

    /**
     * @param int|Product $product
     * @param int ...$imageIds
     * @return int
     * @throws NotAcceptableException
     * @throws Exception
     */
    public function saveProductImages(int|Product $product, int ...$imageIds): int
    {
        if (count($imageIds) > 50) {
            throw new NotAcceptableException('Unable to update these much images on a product.');
        }

        if (is_int($product)) {
            $product = $this->findProductOrFail($product);
        }

        return ProductMedia::exclusive($product->getId(), 'image', ...$imageIds);
    }

    public function getProductSelectedCategoryIds(int $id): iterable
    {
        return ProductCategories::hasManySecondaries($id, key: true);
    }

    public function loadProductCategories(?Product $item): void
    {
        $item->setCategories(ProductCategories::make($item->getId())->hasMany());
    }

    /**
     * @param int|Product $product
     * @param int ...$categoryIds
     * @return int
     * @throws NotAcceptableException
     * @throws Exception
     */
    public function saveProductCategories(int|Product $product, int ...$categoryIds): int
    {
        if (count($categoryIds) > 50) {
            throw new NotAcceptableException('Unable to assign too much categories on a product.');
        }

        if (is_int($product)) {
            $product = $this->findProductOrFail($product);
        }

        return ProductCategories::exclusive($product->getId(), 'product', ...$categoryIds);
    }

    public function getCategories(): iterable
    {
        return $this->getCategoriesService()->loadCategories(ProductCategories::defaultRelationType());
    }

    public function getCategoryTree(): iterable
    {
        return $this->getCategoriesService()->loadCategoryTree(ProductCategories::defaultRelationType());
    }
}