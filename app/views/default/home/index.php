<?php

/**
 * @layout
 */
return function () { ?>

    <h1><?= t('welcome_message', $this->name) ?></h1>

    <p><?= $this->title ?></p>
    <?php
};

