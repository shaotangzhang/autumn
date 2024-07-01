<header class="navbar navbar-expand navbar-sticky sticky-top d-block bg-body z-fixed py-1 py-lg-0 py-xl-1 px-0">
    <div class="container justify-content-start py-2 py-lg-3">
        <?php
        include __DIR__ . '/navbar-toggler.php';
        include __DIR__ . '/navbar-logo.php';

        $this->slot('header-content');
        ?>
        <!-- navbar-categories -->
        <!-- navbar-search-form -->
        <!-- navbar-delivery-options -->

        <div class="d-flex align-items-center gap-md-1 gap-lg-2 ms-auto">
            <!-- navbar-theme-switcher -->
            <!-- navbar-search-form-button -->
            <!-- navbar-delivery-options-mini -->

            <?php
            include __DIR__ . '/navbar-account-button.php';
            include __DIR__ . '/navbar-cart-button.php';
            ?>
        </div>
    </div>

    <!-- navbar-search-form-hidden -->
</header>