<?php

use Autumn\Extensions\Auth\Services\AuthService;

return function(AuthService $service) {

    $user = $service->getCurrentUserDetails();

    ?>


    <header class="mb-3 border-bottom bg-white">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <svg class="bi me-2" width="40" height="32" role="img" aria-label="Bootstrap">
                        <use xlink:href="#bootstrap"></use>
                    </svg>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                        aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse me-auto" id="navbarNavDropdown">
                    <ul class="navbar-nav">
                        <?php include __DIR__ . '/menu-primary.php'; ?>
                    </ul>
                </div>

                <form class="col-12 col-lg-auto mb-3 mb-lg-0 me-lg-3" method="get" action="/" role="search">
                    <input type="search" name="search" class="form-control" placeholder="Search..." aria-label="Search">
                </form>

                <?php
                if($user) {
                    include __DIR__ . '/menu-user.php';
                }else{
                    include __DIR__ . '/menu-login.php';
                }
                ?>
            </div>
        </nav>
    </header>
<?php
};


