<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/11
 */

namespace App\Models\Trading\Products;

use App\Database\Trading\ProductCategoriesEntity;
use App\Models\Blog\Category;

class ProductCategories extends ProductCategoriesEntity
{
    public const MODEL_PRIMARY = Product::class;
    public const MODEL_SECONDARY = Category::class;

}