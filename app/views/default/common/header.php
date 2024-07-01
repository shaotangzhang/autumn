<?php

use Autumn\Extensions\Auth\Services\AuthService;

$session = make(AuthService::class)?->getUserSession();
?>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white">
    <div class="container">
        <a class="navbar-brand" href="<?= site('link', '/') ?>">
            <?= site('title') ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="<?= site('link', '/') ?>"><?=
                        t('menu.home', 'Home') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/products"><?= t('menu.products', 'Products') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/categories"><?= t('menu.categories', 'Categories') ?></a>
                </li>
            </ul>

            <form class="d-flex" action="/search" method="get">
                <input class="form-control me-2" type="search" name="q"
                       placeholder="<?= attr(t('menu.search.placeholder', 'Search')) ?>"
                       aria-label="<?= attr(t('menu.search', 'Search')) ?>">
                <button class="btn btn-outline-success" type="submit"><?= t('menu.search', 'Search') ?></button>
            </form>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <?php if ($session): ?>
                        <a class="nav-link" href="/user/index">
                            <svg data-name="Layer 1" id="Layer_1" viewBox="0 0 48 48" width="16" height="16"
                                 xmlns="http://www.w3.org/2000/svg"><title/>
                                <path d="M24,21A10,10,0,1,1,34,11,10,10,0,0,1,24,21ZM24,5a6,6,0,1,0,6,6A6,6,0,0,0,24,5Z"/>
                                <path d="M42,47H6a2,2,0,0,1-2-2V39A16,16,0,0,1,20,23h8A16,16,0,0,1,44,39v6A2,2,0,0,1,42,47ZM8,43H40V39A12,12,0,0,0,28,27H20A12,12,0,0,0,8,39Z"/>
                            </svg>
                        </a>
                    <?php else: ?>
                        <a class="nav-link" href="/login"><?= t('menu.login', 'Login') ?></a>
                        <a class="nav-link" href="/register"><?= t('menu.register', 'Register') ?></a>
                    <?php endif; ?>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/cart">
                        <svg viewBox="0 0 256 256" width="20" height="20" xmlns="http://www.w3.org/2000/svg">
                            <rect fill="none" height="256" width="256"/>
                            <circle cx="80" cy="216" r="16"/>
                            <circle cx="184" cy="216" r="16"/>
                            <path d="M42.3,72H221.7l-26.4,92.4A15.9,15.9,0,0,1,179.9,176H84.1a15.9,15.9,0,0,1-15.4-11.6L32.5,37.8A8,8,0,0,0,24.8,32H8"
                                  fill="none" stroke="#000" stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="16"/>
                        </svg>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>