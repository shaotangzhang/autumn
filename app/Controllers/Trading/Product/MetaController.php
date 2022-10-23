<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/17
 */

namespace App\Controllers\Trading\Product;

use App\Controllers\Trading\AbstractController;
use App\Models\Trading\Products\ProductMeta;
use App\Services\Blog\BlogServicesTrait;
use Autumn\System\Attributes\ResponseEntity;

class MetaController extends AbstractController
{
    use BlogServicesTrait;


    #[ResponseEntity]
    public function create(ProductMeta $category): ProductMeta
    {
        $service = $this->getMetasService();
        $service->setSiteId($this->getSiteId());

        $category->setType('category:product');
        return $service->createCategory($category);
    }
}