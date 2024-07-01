<?php

/**
 * @layout
 */
return function (iterable $items, ?array $pagination) { ?>

    <div class="banner mb-3">
        <img src="https://via.placeholder.com/1920x200.png/cccccc/ffffff?text=Products" alt="Banner Image"
             class="img-fluid">
    </div>

    <div class="container">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Products</li>
            </ol>
        </nav>

        <h1><?= t('list.title', $this->title ?: 'Products List') ?></h1>

        <?php (include __DIR__ . '/grid.php')($items, $pagination) ?>
    </div>
    <?php
};