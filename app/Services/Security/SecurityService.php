<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/6
 */

namespace App\Services\Security;

use App\Services\AbstractService;
use Autumn\App;
use Autumn\Security\Authentication\UserDetailsChecker;
use Autumn\Security\Crypto\PasswordEncoderFactory;
use Autumn\Security\HttpSecurity;
use Autumn\Security\Interfaces\SecurityConfigureInterface;
use Autumn\System\Interfaces\FilterInterface;

class SecurityService extends AbstractService implements SecurityConfigureInterface
{
    public function configure(HttpSecurity $http): FilterInterface
    {
        $http->setPasswordEncoder(PasswordEncoderFactory::createDelegatingPasswordEncoder());
        $http->setUserDetailsService(App::factory(SecurityUserDetailsService::class));
        $http->setUserDetailsChecker(App::factory(UserDetailsChecker::class));

        $http->matcherCaseSensitive('/([^a-z].*)?')->permitAll();
        $http->matcher('/login/**')->permitAll();
        $http->matcher('/trading/**')->permitAll();

        $http->anyRequests()->authenticated();
        return $http->build();
    }
}