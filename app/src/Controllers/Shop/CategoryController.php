<?php

namespace App\Controllers\Shop;


use Autumn\Exceptions\NotFoundException;
use Autumn\Extensions\Shop\Requests\ProductsRequest;

class CategoryController extends AbstractController
{

    protected string $viewPath = '/shop/category/';
    protected array $languageDomains = ['shop', 'product'];

    public function index(int $page = 1): mixed
    {
        $page = $this->getPageService()->getPage('categories');
        $args = $page?->toArray() ?? [];

        $args['items'] = $this->getProductService()->getCategories(['page' => $page]);

        $args['pagination']['link'] = '/categories';
        $args['pagination']['range'] = 5;

        return $this->view('index', $args);
    }

    public function show(int $id, ProductsRequest $request): mixed
    {
        $product = $this->getProductService()->getCategoryById($id);
        if (!$product) {
            throw NotFoundException::of('Category is not found.');
        }

        $page = $this->getPageService()->getPage('category/detail');
        $args = $page?->toArray() ?? [];
        $args['item'] = $product;


        $args['items'] = $this->getProductService()->searchProducts($request);

        $args['pagination']['link'] = '/categories';
        $args['pagination']['range'] = 5;

        return $this->view('detail', $args);
    }
}
