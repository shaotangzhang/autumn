<?php

namespace App\Controllers;

use Autumn\Extensions\Auth\Services\AuthService;
use Autumn\Extensions\Cms\Services\PageService;
use Autumn\Extensions\Shop\Services\ProductService;
use Autumn\System\Controller;

class AbstractController extends Controller
{
    private ?AuthService $authService = null;
    private ?PageService $pageService = null;

    private ?ProductService $productService = null;

    /**
     * @return AuthService
     */
    public function getAuthService(): AuthService
    {
        return $this->authService ??= make(AuthService::class, true);
    }

    /**
     * @return PageService
     */
    public function getPageService(): PageService
    {
        return $this->pageService ??= make(PageService::class, true);
    }

    /**
     * @return ProductService
     */
    public function getProductService(): ProductService
    {
        return $this->productService ??= make(ProductService::class, true);
    }
}