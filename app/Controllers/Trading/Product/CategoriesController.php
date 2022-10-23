<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/13
 */

namespace App\Controllers\Trading\Product;

use App\Controllers\Trading\AbstractController;
use App\Models\Blog\Category;
use App\Services\Blog\BlogServicesTrait;
use Autumn\System\Attributes\ResponseEntity;
use Autumn\System\View;

class CategoriesController extends AbstractController
{
    use BlogServicesTrait;

    #[ResponseEntity]
    public function create(Category $category): Category
    {
        $service = $this->getCategoriesService();
        $service->setSiteId($this->getSiteId());

        $category->setType('category:product');
        return $service->createCategory($category);
    }
}