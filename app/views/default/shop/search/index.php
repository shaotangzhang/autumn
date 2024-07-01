<?php

/**
 * @layout
 */
return function (string $search, iterable $items, ?array $pagination) { ?>

    <div class="banner mb-3">
        <img src="https://via.placeholder.com/1920x200.png/cccccc/ffffff?text=Search+results" alt class="img-fluid">
    </div>

    <div class="container">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?=
                    t('search.title', 'Search result') ?></li>
            </ol>
        </nav>

        <h1 class="mb-3"><?= tt('search.query_title', $search) ?: $this->title ?: "Search results for `$search`" ?></h1>

        <ul>
            <?php foreach ($items as $item) : ?>
                <li>
                    <a href="/products/<?= $item['id'] ?>"><?= html($item['title']) ?></a>
                    <p class="text-muted"><?= $item['description'] ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
};