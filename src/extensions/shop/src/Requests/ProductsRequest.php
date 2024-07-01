<?php

namespace Autumn\Extensions\Shop\Requests;

use Autumn\System\Requests\QueryRequest;

class ProductsRequest extends QueryRequest
{
    protected array $missing = [
        'limit' => 10
    ];

    protected array $defaults = [
        'limit' => 10
    ];

    protected array $rules = [
        'sort' => 'string|in:price,title,created_at',
        'color' => 'string',
        'size' => 'string',
        'min_price' => 'numeric|min:0',
        'max_price' => 'numeric|min:0',
    ];
}