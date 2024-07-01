<?php

namespace App\Controllers\Shop;

use Autumn\Exceptions\NotFoundException;
use Autumn\Extensions\Shop\Requests\ProductsRequest;


class ProductController extends AbstractController
{
    protected string $viewPath = '/shop/product/';
    protected array $languageDomains = ['shop', 'product'];

    public function index(ProductsRequest $request): mixed
    {
        $page = $this->getPageService()->getPage('products');
        $args = $page?->toArray() ?? [];

        $args['items'] = $this->getProductService()
            ->searchProducts($request, $args['pagination']);

        $args['pagination']['link'] = '/products';
        $args['pagination']['range'] = 5;

        return $this->view('index', $args);
    }

    public function show(int $id): mixed
    {
        $product = $this->getProductService()->getProductById($id);
        if (!$product) {
            throw NotFoundException::of('Product is not found.');
        }

        $page = $this->getPageService()->getPage('product/detail');
        $args = $page?->toArray() ?? [];
        $args['item'] = $product;

        return $this->view('detail', $args);
    }
}
