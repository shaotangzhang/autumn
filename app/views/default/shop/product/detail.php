<?php

/**
 * @layout
 */
return function ($item) { ?>

    <div class="banner mb-3">
        <img src="https://via.placeholder.com/1920x200.png/cccccc/ffffff?text=<?= rawurlencode($item['title']) ?>"
             class="img-fluid" alt>
    </div>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6">
                <img src="<?= attr($item['image']) ?>" class="img-fluid" alt="<?= attr($item['title']) ?>">
            </div>
            <div class="col-md-6">
                <h1><?= html($item['title']) ?></h1>
                <p class="price">$<?= number_format(floatval($item['price']), 2) ?></p>
                <p class="description"><?= $item['content'] ?? nl2br($item['description'] ?? '') ?></p>
                <a href="/cart/add/<?= $item['id'] ?>" class="btn btn-primary"><?=
                    t('shop.add_to_cart', 'Add to Cart') ?></a>
            </div>
        </div>
    </div>
    <?php
};