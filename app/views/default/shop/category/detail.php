<?php

/**
 * @layout
 */
return function ($item, iterable $items, ?array $pagination) { ?>

    <div class="banner mb-3">
        <img src="https://via.placeholder.com/1920x200.png/cccccc/ffffff?text=<?= rawurlencode($item['title']) ?>"
             class="img-fluid" alt>
    </div>

    <div class="container mt-5">
        <h1><?= html($item['title']) ?></h1>
        <p><?= nl2br($item['description'] ?? '') ?></p>

        <?php (include __DIR__ . '/../product/grid.php')($items, $pagination) ?>
    </div>
    <?php
};