<?php

return function (iterable $items = []) { ?>
    <!-- Featured Products -->
    <div class="container my-5">
        <h3><?= t('products.featured.title', 'Featured Products') ?></h3>
        <div class="row">
            <?php foreach ($items as $product): ?>
                <div class="col-md-4">
                    <div class="card mb-4 shadow-sm h-100">
                        <div class="ratio ratio-21x9">
                            <img src="<?= $product['image'] ?>" alt="<?= $product['title'] ?>">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= $product['title'] ?></h5>
                            <p class="card-text"><?= $product['description'] ?></p>
                            <p class="card-text">$<?= $product['price'] ?></p>
                            <a href="/products/<?= $product['id'] ?>" class="btn btn-primary"><?=
                                t('products.view_details', 'View Details') ?></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php };
