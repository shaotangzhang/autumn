<?php
/**
 * @layout
 */
return function (string $error = null, string $redirect = null) { ?>

    <style>
        .form-signin {
            max-width: 330px;
            padding: 5rem 15px 15px;
        }

        .form-signin .form-floating:focus-within {
            z-index: 2;
        }

        .form-signin input[type="email"] {
            margin-bottom: -1px;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }

        .form-signin input[type="password"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
    </style>

    <form method="post" class="form-signin m-auto">
        <input type="hidden" name="redirect" value="<?= attr($redirect) ?>">

        <?php if ($error) : ?>
            <div class="alert alert-danger" role="alert"><?= $error ?></div>
        <?php endif; ?>

        <h1 class="h3 mb-3 fw-normal"><?= t('login.title', 'Please sign in') ?></h1>

        <div class="form-floating">
            <input type="email" name="username" class="form-control" id="floatingInput"
                   placeholder="name@example.com">
            <label for="floatingInput"><?= t('labels.username', 'Email address') ?></label>
        </div><?= t('labels.username', 'Username') ?>:
        <div class="form-floating">
            <input type="password" name="password" class="form-control" id="floatingPassword"
                   placeholder="Password">
            <label for="floatingPassword"><?= t('labels.password', 'Password') ?></label>
        </div>

        <?php if (env('USER_LOGIN_REMEMBER_ME')) : ?>
            <div class="checkbox mb-3">
                <label>
                    <input type="checkbox" value="remember-me">
                    <?= t('labels.remember_me', 'Remember me') ?>
                </label>
            </div>
        <?php endif; ?>

        <button class="w-100 btn btn-lg btn-primary" type="submit"><?= t('labels.submit', 'Sign in') ?></button>
    </form>

    <?php
};