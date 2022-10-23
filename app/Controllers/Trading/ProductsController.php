<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/8
 */

namespace App\Controllers\Trading;

use App\Models\Trading\Products\Product;
use App\Models\Trading\Products\ProductCategories;
use Autumn\Database\Exceptions\DbException;
use Autumn\Database\Schema;
use Autumn\Http\Exceptions\NotFoundException;
use Autumn\System\Attributes\ResponseEntity;
use Autumn\System\View;

class ProductsController extends AbstractController
{
    /**
     * @throws NotFoundException
     */
    public function index(
        ?string $search,
        ?string $status,
        ?int    $parentId,
        ?int    $categoryId,
        ?int    $limit,
        ?int    $page): View
    {
        $title = 'Products';

        $service = $this->getProductsService();
        $service->setSiteId($this->getSiteId());

        if ($parentId) {
            $parent = $service->findProductOrFail($parentId);
            $title .= ' of ' . $parent->getTitle();
        } else {
            $parent = null;
        }

        $query = $service->createQuery(min($limit, 500) ?: 25, $page);

        if ($status) $query->field('status')->equals(strtolower($status));
        if ($parentId !== null) $query->field('parentId')->equals($parentId);

        if ($categoryId) {
            $category = $this->getProductsService()->findCategoryOrFail($categoryId);
            $title .= ' [' . $category->getTitle() . ']';

            $query->innerJoin(ProductCategories::entity_name(), 'PM', 'id', 'primaryId');
            $query->field('PM.secondaryId')->equals($categoryId);
        }

        return $this->fetch('products/index', [
            'title' => $title,
            'items' => $query->query(),
            'pagination' => $query->pagination(),
            'status' => $status,
            'search' => $search,
            'parent' => $parent,
            'parentId' => $parentId,
            'categories' => $service->getCategories()
        ]);
    }

    /**
     * @throws NotFoundException
     */
    public function show(?int $id): View
    {
        $service = $this->getProductsService();
        $service->setSiteId($this->getSiteId());

        $item = $service->findProductOrFail($id);
        $service->loadProductImages($item);

        $parent = ($parentId = $item->getParentId())
            ? $service->findProductOrFail($parentId)
            : null;

        return $this->fetch('products/detail', [
            'title' => 'Product detail',
            'parentId' => $parentId,
            'parent' => $parent,
            'item' => $item
        ]);
    }

    /**
     * @param Product $item
     * @param int|string|array|null $images
     * @param int|string|array|null $categories
     * @return Product
     * @throws DbException
     */
    #[ResponseEntity]
    public function create(
        Product               $item,
        int|string|array|null $images,
        int|string|array|null $categories): Product
    {
        $images = filter_var_array((array)$images, FILTER_VALIDATE_INT, false);
        $categories = filter_var_array((array)$categories, FILTER_VALIDATE_INT, false);

        return Schema::transaction(function () use ($item, $images, $categories) {
            $service = $this->getProductsService();
            $service->setSiteId($this->getSiteId());

            if ($product = $service->createProduct($item)) {
                $service->saveProductImages($product, ...$images);
                $service->updateProductImage($product);

                $service->saveProductCategories($product, ...$categories);
            }
            return $product;
        }, 'debug_error');
    }

    /**
     * @param int $id
     * @param array $item
     * @param int|string|array|null $images
     * @param int|string|array|null $categories
     * @return array
     * @throws DbException
     */
    #[ResponseEntity]
    public function update(int                   $id,
                           array                 $item,
                           int|string|array|null $images,
                           int|string|array|null $categories): array
    {
        $images = filter_var_array((array)$images, FILTER_VALIDATE_INT, false);
        $categories = filter_var_array((array)$categories, FILTER_VALIDATE_INT, false);

        return Schema::transaction(function () use ($id, $item, $images, $categories) {

            $service = $this->getProductsService();
            $service->setSiteId($this->getSiteId());

            $product = $service->findProductOrFail($id);
            $service->updateProduct($product, array_merge([
                'fragile' => false,
                'dangerous' => false,
            ], $item));

            $service->saveProductImages($id, ...$images);
            $service->updateProductImage($product, reset($images));

            $service->saveProductCategories($product, ...$categories);

            return [
                'action' => 'update',
                'result' => true,
            ];
        }, 'debug_error');
    }
}