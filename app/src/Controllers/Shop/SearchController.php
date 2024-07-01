<?php

namespace App\Controllers\Shop;

use Autumn\System\Requests\QueryRequest;

class SearchController extends AbstractController
{
    protected string $viewPath = '/shop/search/';
    protected array $languageDomains = ['shop'];

    public function index(QueryRequest $request): mixed
    {
        $page = $this->getPageService()->getPage('search');
        $args = $page?->toArray() ?? [];

        $args['items'] = $this->getProductService()
            ->searchProducts($request, $args['pagination']);

        $args['pagination']['link'] = '/products';
        $args['pagination']['range'] = 5;
        $args['search'] = $request['q'] ?? $request['search'] ?? null;

        return $this->view('index', $args);
    }
}
