<?php

return function () {

    $this->defineSlot('head', fn() => include __DIR__ . '/head.php');
    $this->defineSlot('header', include __DIR__ . '/header.php');
    $this->defineSlot('footer', fn() => include __DIR__ . '/footer.php');
    $this->defineSlot('foot', fn() => include __DIR__ . '/foot.php');

    include __DIR__ . '/starter.php';
};