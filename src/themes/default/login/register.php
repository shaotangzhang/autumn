<?php
/**
 * @layout
 */
return function (string $error = null, string $redirect = null) { ?>

    <style>
        .form-sign-up {
            max-width: 330px;
            padding: 5rem 15px 15px;
        }

        .form-sign-up .form-floating:focus-within {
            z-index: 2;
        }

        .form-sign-up input[type="email"],
        .form-sign-up input[type="text"] {
            margin-bottom: -1px;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }

        .form-sign-up input[type="password"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
    </style>

    <form method="post" class="form-sign-up m-auto">
        <input type="hidden" name="redirect" value="<?= attr($redirect ?: env('USER_LOGIN_DEFAULT_REDIRECT')) ?>">

        <?php if ($error) : ?>
            <div class="alert alert-danger" role="alert"><?= $error ?></div>
        <?php endif; ?>

        <h1 class="h3 mb-3 fw-normal"><?= t('register.title', 'Please register') ?></h1>

        <div class="form-floating">
            <input type="text" name="name" class="form-control" id="floatingName" placeholder="Full Name" required>
            <label for="floatingName"><?= t('labels.name', 'Full Name') ?></label>
        </div>

        <div class="form-floating">
            <input type="email" name="email" class="form-control" id="floatingEmail" placeholder="name@example.com"
                   required>
            <label for="floatingEmail"><?= t('labels.email', 'Email address') ?></label>
        </div>

        <div class="form-floating">
            <input type="text" name="username" class="form-control" id="floatingUsername" placeholder="Username"
                   required>
            <label for="floatingUsername"><?= t('labels.username', 'Username') ?></label>
        </div>

        <div class="form-floating">
            <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password"
                   required>
            <label for="floatingPassword"><?= t('labels.password', 'Password') ?></label>
        </div>

        <div class="form-floating">
            <input type="password" name="confirm_password" class="form-control" id="floatingConfirmPassword"
                   placeholder="Confirm Password" required>
            <label for="floatingConfirmPassword"><?= t('labels.confirm_password', 'Confirm Password') ?></label>
        </div>

        <div class="checkbox mb-3">
            <label>
                <input type="checkbox" name="terms" value="accepted" required>
                <?= t('labels.terms', 'I accept the terms and conditions') ?>
            </label>
        </div>

        <button class="w-100 btn btn-lg btn-primary" type="submit"><?= t('labels.submit', 'Register') ?></button>
    </form>

    <?php
};
