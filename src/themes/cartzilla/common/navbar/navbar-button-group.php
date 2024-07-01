<!-- Button group -->
<div class="d-flex align-items-center gap-md-1 gap-lg-2 ms-auto">

    <!-- navbar-theme-switcher -->

    <!-- Search toggle button visible on screens < 768px wide (md breakpoint) -->
    <button type="button"
            class="btn btn-icon fs-xl btn-outline-secondary border-0 rounded-circle animate-shake d-md-none"
            data-bs-toggle="collapse" data-bs-target="#searchBar" aria-controls="searchBar"
            aria-label="Toggle search bar">
        <i class="ci-search animate-target"></i>
    </button>

    <!-- Delivery options button visible on screens < 1200px wide (xl breakpoint) -->
    <button type="button"
            class="btn btn-icon fs-lg btn-outline-secondary border-0 rounded-circle animate-scale d-xl-none"
            data-bs-toggle="offcanvas" data-bs-target="#deliveryOptions" aria-controls="deliveryOptions"
            aria-label="Toggle delivery options offcanvas">
        <i class="ci-map-pin animate-target"></i>
    </button>

    <!-- Account button visible on screens > 768px wide (md breakpoint) -->
    <a class="btn btn-icon fs-lg btn-outline-secondary border-0 rounded-circle animate-shake d-none d-md-inline-flex"
       href="account-signin.html">
        <i class="ci-user animate-target"></i>
        <span class="visually-hidden">Account</span>
    </a>

    <!-- Cart button -->
    <button type="button"
            class="btn btn-icon fs-xl btn-outline-secondary position-relative border-0 rounded-circle animate-scale"
            data-bs-toggle="offcanvas" data-bs-target="#shoppingCart" aria-controls="shoppingCart"
            aria-label="Shopping cart">
                <span class="position-absolute top-0 start-100 badge fs-xs text-bg-primary rounded-pill ms-n3 z-2"
                      style="--cz-badge-padding-y: .25em; --cz-badge-padding-x: .42em">8</span>
        <i class="ci-shopping-cart animate-target"></i>
    </button>
</div>