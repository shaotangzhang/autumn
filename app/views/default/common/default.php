<?php

return function () {
    $this->defineSlot('header', fn() => include __DIR__ . '/header.php');
    $this->defineSlot('footer', fn() => include __DIR__ . '/footer.php');
    include __DIR__ . '/starter.php';
};