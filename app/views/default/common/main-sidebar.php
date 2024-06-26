<!DOCTYPE html>
<html lang="<?= env('SITE_LANG', 'en') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $pageTitle ?? $this->pageTitle ?? $title ?? $this->title ?? env('SITE_NAME', translate('Untitled page')); ?></title>

    <?php $this->slot('head'); ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="/assets/css/common.css" rel="stylesheet">

    <?php $this->slot('styles'); ?>
</head>

<body class="<?= $bodyExtClass ?? $extClass ?? 'bg-light' ?>">

<a href="#mainContent" aria-label="skip to contents"></a>

<?php $this->slot('top'); ?>

<header class="border-bottom bg-white"><?php $this->slot('header'); ?></header>

<?php $this->slot('left'); ?>

<div class="container mt-3">
    <div class="row">
        <main class="col-md-8">
            <?php
            $this->slot('before');
            ?>

            <a id="mainContent" hidden></a>

            <?php
            $this->contents();

            $this->slot('after');
            ?>
        </main>
        <aside class="col-md-4"><?php $this->slot('sidebar'); ?></aside>
    </div>
</div>

<?php $this->slot('right'); ?>

<footer><?php $this->slot('footer'); ?></footer>

<?php $this->slot('bottom'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

<?php $this->slot('scripts'); ?>
<?php $this->slot('foot'); ?>

<?php env('DEBUG') && include __DIR__ . '/debug.php' ?>
</body>
</html>
