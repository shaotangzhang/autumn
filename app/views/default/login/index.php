<?php

/**
 * @layout
 */
return function (string $error = null, string $redirect = null) { ?>

    <form method="post" class="card">
        <input type="hidden" name="redirect" value="<?= attr($redirect) ?>">

        <?php if ($error) : ?>
            <div class="alert alert-danger" role="alert"><?= $error ?></div>
        <?php endif; ?>

        <div class="card-body">
            <div class="mb-3">
                <label for="form-username"><?= t('labels.username', 'Username') ?>:</label>
                <input name="username" type="text" id="form-username" class="form-control">
            </div>
            <div class="mb-3">
                <label for="form-password"><?= t('labels.password', 'Password') ?>:</label>
                <input name="password" type="text" id="form-password" class="form-control">
            </div>

            <div class="mb-3">
                <label></label>
                <button type="submit" class="btn btn-primary"><?= t('labels.submit', 'Submit') ?></button>
            </div>
        </div>
    </form>

<?php };