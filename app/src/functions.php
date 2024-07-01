<?php

use Autumn\Database\Interfaces\SiteInterface;
use Autumn\Extensions\Auth\Interfaces\UserDetailsInterface;
use Autumn\Extensions\Auth\Services\AuthService;

if (!function_exists('user')) {
    function user(): UserDetailsInterface
    {
        return make(AuthService::class)?->getCurrentUserDetails();
    }
}

if (!function_exists('userInfo')) {
    function userInfo(): UserDetailsInterface
    {
        return make(AuthService::class)?->getCurrentUserInfo();
    }
}

if (!function_exists('site')) {
    /**
     * Returns the site instance or a configuration of the site.
     * Optionally, the configuration value can be translated.
     *
     * @param string|null $property The property of the site configuration to retrieve.
     * @param mixed|null $default The default value to return if the property is not set.
     * @param bool $translated Whether to translate the configuration value.
     * @return mixed The site instance or the configuration value.
     */
    function site(string $property = null, mixed $default = null, bool $translated = false): mixed
    {
        static $site;

        // Get the site instance and cache it
        $site ??= make(SiteInterface::class, null, true) ?? false;

        if ($site) {
            // Return the site instance if no arguments are provided
            if (!func_num_args()) {
                return $site;
            }

            // Get the property value from the site instance
            $value = $site->$property;
        } elseif (empty($property)) {
            return null;
        }

        // Get the property value from the environment configuration
        $value ??= env('SITE_' . strtoupper($property));

        // Translate the value if needed and it's a string
        if ($translated && is_string($value)) {
            $value = t('site.' . $property, $value);
        }

        // Return the property value or the default value if property is not set
        return $value ?? $default;
    }
}