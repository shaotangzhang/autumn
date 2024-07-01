<?php

use Autumn\Extensions\Auth\Services\AuthService;

?>
<!-- Account button visible on screens > 768px wide (md breakpoint) -->
<a class="btn btn-icon fs-lg btn-outline-secondary border-0 rounded-circle animate-shake d-none d-md-inline-flex"
   href="<?php

   if ($userDetails = make(AuthService::class)?->getCurrentUserDetails()) {
       echo '/user';
   } else {
       echo '/login';
   }

   ?>">
    <i class="ci-user animate-target"></i>
    <span class="visually-hidden"><?= $userDetails?->getUsername() ?: t('Account') ?></span>
</a>