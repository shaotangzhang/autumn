<?php

return function (iterable $items = [], array $pagination = null) { ?>

    <div class="row mb-3">
        <?php foreach ($items as $item): ?>
            <div class="col-md-4">
                <div class="card mb-4 shadow-sm h-100">
                    <div class="ratio ratio-21x9">
                        <img src="<?= $item['image'] ?>" alt="<?= $item['title'] ?>">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?= $item['title'] ?></h5>
                        <p class="card-text"><?= $item['description'] ?? null ?></p>
                        <p class="card-text">$<?= $item['price'] ?></p>
                        <a href="/products/<?= $item['id'] ?>" class="btn btn-primary"><?=
                            t('products.view_details', 'View Details') ?></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?= component('pagination', $pagination); ?>

    <?php
};