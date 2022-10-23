<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/8
 */

namespace App\Database\Trading;

use App\Database\Blog\MediaEntity;
use Autumn\Database\AbstractRelation;

class ProductMediaEntity extends AbstractRelation
{
    public const ENTITY_NAME = 'trading_product_media';
    public const DEFAULT_TYPE = 'trading-product';

    public const MODEL_PRIMARY = ProductEntity::class;
    public const MODEL_SECONDARY = MediaEntity::class;

}