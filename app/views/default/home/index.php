<?php

/**
 * @layout
 */
return function (iterable $banners = [], iterable $products = [], $categories = []) {
    (include __DIR__ . '/carousel.php')($banners);
    (include __DIR__ . '/featured-products.php')($products);
    (include __DIR__ . '/categories.php')($categories);
};

