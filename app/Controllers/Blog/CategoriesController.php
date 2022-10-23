<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/11
 */

namespace App\Controllers\Blog;

use App\Models\Blog\Category;
use Autumn\System\Attributes\ResponseEntity;

class CategoriesController extends AbstractController
{
    #[ResponseEntity]
    public function create(Category $category): Category
    {
        return $this->getCategoriesService()->createCategory($category);
    }
}