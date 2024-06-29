<?php
/**
 * @layout
 */
return function () { ?>

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
        <h1 class="h3 mb-3 fw-normal"><?= t('Please sign in') ?></h1>

        <div class="form-floating">
            <input type="email" name="username" class="form-control" id="floatingInput"
                   placeholder="name@example.com">
            <label for="floatingInput"><?= t('Email address') ?></label>
        </div>
        <div class="form-floating">
            <input type="password" name="password" class="form-control" id="floatingPassword"
                   placeholder="Password">
            <label for="floatingPassword"><?= t('Password') ?></label>
        </div>

        <div class="checkbox mb-3">
            <label>
                <input type="checkbox" value="remember-me">
                <?= t('Remember me') ?>
            </label>
        </div>
        <button class="w-100 btn btn-lg btn-primary" type="submit"><?= t('Sign in') ?></button>
    </form>

    <?php
};