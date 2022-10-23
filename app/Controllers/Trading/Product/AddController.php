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

class AddController extends AbstractController
{
    /**
     * @throws NotFoundException
     */
    public function index(?int $parentId): View
    {
        $service = $this->getProductsService();
        $service->setSiteId($this->getSiteId());

        if ($parentId) {
            $parent = $service->findProductOrFail($parentId);
        } else {
            $parent = null;
        }

        return $this->fetch('products/add', [
            'title' => 'Add new product' . ($parent ? ' : ' . $parent['title'] : ''),
            'parentId' => $parentId,
            'parent' => $parent ?? null,
            'categories' => get_list_of_data($service->getCategories(), 'id')
        ]);
    }
}