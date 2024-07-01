<?php

/**
 * @layout
 */
return function (iterable $items, ?array $pagination) { ?>

    <div class="banner mb-3">
        <img src="https://via.placeholder.com/1920x200.png/cccccc/ffffff?text=Categories" alt class="img-fluid">
    </div>

    <div class="container">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?=
                    $title = t('list.title', $this->title ?: 'Categories') ?></li>
            </ol>
        </nav>

        <h1><?= $title ?></h1>

        <ul>
            <?php foreach ($items as $item) : ?>
                <li><a href="/categories/<?= $item['id'] ?>"><?= html($item['title']) ?></a></li>
            <?php endforeach; ?>
        </ul>

        <?= component('pagination', $pagination); ?>
    </div>
    <?php
};