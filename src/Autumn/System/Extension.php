<?php

namespace Autumn\System;

use Autumn\App;
use Autumn\Database\Migration\Migration;
use Autumn\Interfaces\ContextInterface;
use Autumn\System\ServiceContainer\ServiceContainerInterface;
use Autumn\System\ServiceContainer\ServiceProviderInterface;
use Autumn\Traits\ContextInterfaceTrait;

/**
 * Base class for application extensions.
 */
abstract class Extension implements ContextInterface, ServiceProviderInterface
{
    use ContextInterfaceTrait;

    /**
     * The version of the extension.
     */
    public const VERSION = "1.0.0";

    /**
     * List of required extensions and their minimum versions.
     */
    public const REQUIRED_EXTENSIONS = [];

    /**
     * List of required middlewares that should be applied to the application.
     */
    public const REQUIRED_MIDDLEWARES = [];

    /**
     * List of services to be registered with the service container.
     */
    public const REGISTERED_SERVICES = [];

    /**
     * List of entities or other resources registered by the extension.
     */
    public const REGISTERED_ENTITIES = [];

    /**
     * Storage for extension root paths.
     */
    private static array $extensionRoots = [];

    /**
     * Configuration data for the extension.
     */
    private static array $configurations = [];

    /**
     * Verifies if all required extensions are mounted and their versions are compatible.
     *
     * @throws \RuntimeException If a required extension is not mounted or version is too low.
     */
    public static function verifyExtensionRequirements(): void
    {
        foreach (static::REQUIRED_EXTENSIONS as $extension => $version) {
            if (is_int($extension)) {
                $extension = $version;
                $version = '0.0.1';
            }

            if (!is_subclass_of($extension, self::class)) {
                throw new \RuntimeException(sprintf('The extension `%s` is required to be mounted.', $extension));
            }

            if (version_compare($version, $extension::VERSION, '>')) {
                throw new \RuntimeException(sprintf('The version of mounted extension `%s` is too lower.', $extension));
            }
        }
    }

    /**
     * Retrieves a configuration value from the extension's configurations.
     *
     * @param string $key The configuration key to retrieve.
     * @param mixed $default Optional. Default value if key does not exist.
     *
     * @return mixed The configuration value, or $default if key is not found.
     */
    public static function env(string $key, mixed $default = null): mixed
    {
        if (!isset(self::$configurations[static::class])) {
            self::$configurations = [];

            // Load configurations from the config/.env file
            if ($file = realpath(static::path('config', '.env'))) {
                $data = parse_ini_file($file, true);
                if (is_array($data)) {
                    self::$configurations[static::class] = $data;
                }
            }
        }

        return self::$configurations[static::class][$key] ?? $default;
    }

    /**
     * Retrieves the filesystem path for the extension.
     *
     * @param string ...$args Additional path segments to append.
     *
     * @return string The filesystem path for the extension.
     */
    public static function path(string ...$args): string
    {
        if (!isset(self::$extensionRoots[static::class])) {
            $reflection = new \ReflectionClass(static::class);
            self::$extensionRoots[static::class] = dirname($reflection->getFileName(), 2);
        }

        array_unshift($args, self::$extensionRoots[static::class]);
        return implode(DIRECTORY_SEPARATOR, $args);
    }

    /**
     * Retrieves the real filesystem path for the extension.
     *
     * @param string ...$args Additional path segments to append.
     *
     * @return string The real filesystem path for the extension.
     */
    public static function realpath(string ...$args): string
    {
        return realpath(static::path(...$args));
    }

    /**
     * Registers services provided by the extension with the service container.
     *
     * @param ServiceContainerInterface $container The service container to register services with.
     */
    public static function register(ServiceContainerInterface $container): void
    {
        static::verifyExtensionRequirements();

        foreach (self::REGISTERED_SERVICES as $service => $concrete) {
            $container->bind($service, $concrete);
        }
    }

    /**
     * Boots the extension by applying required middlewares to the application.
     *
     * @param Application $application The application instance to apply middlewares to.
     */
    public static function boot(Application $application): void
    {
        $application->applyMiddleware(...self::REQUIRED_MIDDLEWARES);
    }

    /**
     * Loads routes from configuration files based on application name and prefix.
     *
     * @param string $prefix The route prefix to apply to loaded routes.
     * @param array|null $options Optional. Additional options for route loading.
     *
     * @return Route|null The loaded Route object, or null if no routes were loaded.
     */
    public static function routes(string $prefix = '', array $options = null): ?Route
    {
        return static::mount(null, $prefix, $options);
    }

    public static function mount(string $appName = null, string $prefix = '', array $options = null): ?Route
    {
        if ($appName = strtolower($appName ?? App::name())) {
            $routes = "routes-$appName.php";
        } else {
            $routes = 'routes.php';
        }

        if ($file = realpath(static::path('config', $routes))) {
            return Route::group($prefix, fn() => include_once $file, $options);
        }

        return null;
    }

    public static function migrate(): void
    {
        Migration::context()->registerEntity(...static::REGISTERED_ENTITIES);
    }
}
