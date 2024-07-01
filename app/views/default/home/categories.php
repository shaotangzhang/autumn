<?php

return function (iterable $categories = []) { ?>
    <!-- Categories -->
    <div class="container my-5">
        <h2>Categories</h2>
        <div class="row">
            <?php foreach ($categories as $category): ?>
                <div class="col-md-3">
                    <div class="card mb-4 shadow-sm">
                        <img src="<?= $category['image'] ?>" class="card-img-top" alt="<?= $category['title'] ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= $category['title'] ?></h5>
                            <a href="/categories/<?= $category['id'] ?>" class="btn btn-secondary">View Products</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
};