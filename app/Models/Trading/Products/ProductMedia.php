<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/8
 */

namespace App\Models\Trading\Products;

use App\Database\Trading\ProductMediaEntity;
use App\Models\Blog\Media;

class ProductMedia extends ProductMediaEntity
{
    public const MODEL_PRIMARY = Product::class;
    public const MODEL_SECONDARY = Media::class;

    public const DEFAULT_TYPE = 'standard';


}