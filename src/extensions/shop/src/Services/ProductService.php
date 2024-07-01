<?php

namespace Autumn\Extensions\Shop\Services;

use Autumn\Extensions\Cms\Models\Page\Page;
use Autumn\Extensions\Shop\Requests\ProductsRequest;
use Autumn\System\Requests\QueryRequest;

class ProductService extends AbstractService
{
    public function getLatest(): iterable
    {
        return $this->dummies('product', 'featured')
            ?: Page::findBy([])->withoutTrashed()->orderBy('created_at', true);
    }

    public function getFeatured(): iterable
    {
        return $this->dummies('product', 'featured');
    }

    public function getCategories(array $context = null): iterable
    {
        return $this->dummies('category');
    }

    public function searchProducts(QueryRequest $request, array &$pagination = null): iterable
    {
        $data = $request->filter();

        $pagination = [
            'total' => 100,
            'page' => $data['page'] ?? null,
            'limit' => $data['limit'] ?? null,
        ];

        return $this->dummies('product', 'featured');
    }

    public function getProductById(int $id): array
    {
        return $this->getFeatured()[$id - 1];
    }

    public function getCategoryById(int $id): array
    {
        return $this->getCategories()[$id - 1];
    }

}