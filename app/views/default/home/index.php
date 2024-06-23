<?php

$this->defineLayoutSlot('header', fn() => 'Welcome to my channel.');

$this->set('title', 'Welcome to my channel.');

/**
 * @layout
 */
return function () {

    $this->set('title', 'Welcome to my channel invisible.', onlyIfNotSet: true);

    ?>

    <h1><?= $this->title ?></h1>

<?php };
