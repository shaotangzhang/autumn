<?php

return function (?int $total, ?int $limit, ?int $page, ?string $link, string $param = 'page', int $range = 5, string $align = 'center') {

    if ($total < 1) return;
    if ($limit < 1) return;

    $page = (int)max(1, $page);
    $totalPages = (int)ceil($total / $limit);

    $range = (int)min(20, max(1, $range));
    $start = max(1, $page - floor($range / 2));
    $end = (int)min($totalPages, $start + $range - 1);
    $start = (int)max(1, $end - $range + 1);

    if ($link === null) {
        $link = '';
        $args = $_GET;
    } else {
        $args = [];
        $linkWithoutHash = explode('#', $link)[0];
        if (str_contains($linkWithoutHash, '?')) {
            [$link, $queryOfLink] = explode('?', $linkWithoutHash, 2);
            if (!empty($queryOfLink)) {
                parse_str($queryOfLink, $args);
            }
        }
    }

    $buildLink = fn($n) => $link . '?' . http_build_query(array_merge($args, [$param ?: 'page' => $n]));

    ?>
    <!-- Pagination -->
    <nav aria-label="Page navigation" class="d-flex justify-content-<?= $align ?> mb-3">
        <?php $this->slot('header') ?>
        <ul class="pagination">
            <?php $this->slot('before') ?>

            <?php if ($start > 1) : ?>
                <li class="page-item"><a class="page-link" href="<?= $buildLink(1) ?>"><span
                                aria-hidden="true">&laquo;</span></a></li>
            <?php endif; ?>

            <?php for ($n = $start; $n <= $end; $n++):
                if ($n === $page) : ?>
                    <li class="page-item active" aria-current="page">
                        <a class="page-link" href="javascript:"><?= $n ?></a>
                    </li>
                <?php else: ?>
                    <li class="page-item"><a class="page-link" href="<?= $buildLink($n) ?>"><?= $n ?></a></li>
                <?php endif;
            endfor; ?>

            <?php if ($end < $totalPages) : ?>
                <li class="page-item"><a class="page-link" href="<?= $buildLink($totalPages) ?>"><span
                                aria-hidden="true">&raquo;</span></a></li>
            <?php endif; ?>

            <?php $this->slot('after') ?>
        </ul>
        <?php $this->slot('footer') ?>
    </nav>

    <?php
};