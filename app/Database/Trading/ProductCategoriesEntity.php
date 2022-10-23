<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/11
 */

namespace App\Database\Trading;

use App\Database\Blog\PostEntity;
use Autumn\Database\AbstractRelation;

class ProductCategoriesEntity extends AbstractRelation
{
    public const ENTITY_NAME = 'trading_product_categories';
    public const DEFAULT_TYPE = 'product';
    public const MODEL_PRIMARY = ProductEntity::class;
    public const MODEL_SECONDARY = PostEntity::class;
}