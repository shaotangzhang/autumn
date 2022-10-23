<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/8
 */

namespace App\Controllers\Trading\Product;

use App\Controllers\Trading\AbstractController;
use Autumn\Http\Exceptions\NotFoundException;
use Autumn\System\View;

class EditController extends AbstractController
{
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

        return $this->fetch('products/edit', [
            'title' => 'Edit product',
            'parentId' => $parentId,
            'parent' => $parent,
            'item' => $item,
            'selectedCategories'=>$service->getProductSelectedCategoryIds($item->getId()),
            'categories'=>get_list_of_data($service->getCategories(), 'id')
        ]);
    }
}