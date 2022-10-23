<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/8
 */

namespace App\Services\Trading;

use App\Services\Trading\Inventory\ProductsService;
use Autumn\App;

trait TradingServicesTrait
{
    public function getProductsService(): ProductsService
    {
        return App::factory(ProductsService::class);
    }
}