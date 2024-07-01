<?php

/**
 * @layout
 */
return function () { ?>

    <h1><?= tt('welcome_message', $this->name) ?></h1>

    <p><?= $this->title ?></p>
    <?php
};

