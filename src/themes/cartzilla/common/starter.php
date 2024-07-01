<!DOCTYPE html>
<html lang="<?= env('SITE_LANG', 'en') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- SEO Meta Tags -->
    <title><?= html($pageTitle ?? $this->pageTitle ?? $title ?? $this->title ?? site('title', 'Untitled page')); ?></title>

    <?php $this->slot('head'); ?>

    <!-- Webmanifest + Favicon / App icons -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="manifest" href="/theme/cartzilla/manifest.json">
    <link rel="icon" type="image/png" href="/theme/cartzilla/app-icons/icon-32x32.png" sizes="32x32">
    <link rel="apple-touch-icon" href="/theme/cartzilla/app-icons/icon-180x180.png">

    <!-- Viewport -->
    <meta name="viewport"
          content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">

    <!-- Preloaded local web font (Inter) -->
    <link rel="preload" href="/theme/cartzilla/fonts/inter-variable-latin.woff2" as="font" type="font/woff2"
          crossorigin>

    <!-- Font icons -->
    <link rel="preload" href="/theme/cartzilla/icons/cartzilla-icons.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/theme/cartzilla/icons/cartzilla-icons.min.css">

    <!-- Vendor styles -->
    <link rel="stylesheet" href="/theme/cartzilla/vendor/choices.js/public/assets/styles/choices.min.css">

    <!-- Bootstrap + Theme styles -->
    <link rel="preload" href="/theme/cartzilla/css/theme.min.css" as="style">
    <link rel="stylesheet" href="/theme/cartzilla/css/theme.min.css" id="theme-styles">

    <?php $this->slot('styles'); ?>
</head>

<body class="<?= $bodyExtClass ?? $extClass ?? 'bg-white' ?>">

<a href="#mainContent" aria-label="skip to contents"></a>

<?php $this->slot('top'); ?>

<?php include __DIR__ . '/navbar/default.php'; ?>
<?php $this->slot('header'); ?>

<?php $this->slot('sidebar'); ?>

<main class="w-100">
    <?php
    $this->slot('before');
    ?>

    <a id="mainContent" hidden></a>

    <?php
    $this->contents();

    $this->slot('after');
    ?>
</main>

<?php $this->slot('endbar'); ?>

<footer><?php $this->slot('footer'); ?></footer>

<?php $this->slot('bottom'); ?>

<!-- Vendor scripts -->
<script src="/theme/cartzilla/vendor/choices.js/public/assets/scripts/choices.min.js"></script>

<!-- Bootstrap + Theme scripts -->
<script src="/theme/cartzilla/js/theme.min.js"></script>

<?php $this->slot('scripts'); ?>
<?php $this->slot('foot'); ?>

<?php env('DEBUG') && include __DIR__ . '/debug.php' ?>
</body>
</html>
