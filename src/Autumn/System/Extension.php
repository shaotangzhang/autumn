<?php

namespace Autumn\System;

use Autumn\App;
use Autumn\Interfaces\SingletonInterface;
use Autumn\System\ServiceContainer\ServiceContainerInterface;
use Autumn\System\ServiceContainer\ServiceProviderInterface;

/**
 * Class Extension
 *
 * Represents an extension module within the application.
 */
abstract class Extension extends Service implements ServiceProviderInterface
{
    public const VERSION = "1.0.0";
    public const REQUIRED_EXTENSIONS = [];
    public const REQUIRED_MIDDLEWARES = [];
    public const REGISTERED_SERVICES = [];
    public const REGISTERED_ENTITIES = [];
    private static array $extensionRoots = [];

    private ?ServiceContainerInterface $container = null;

    private static ?array $configurations = null;

    /**
     * Extension constructor.
     *
     * Registers the extension with the application and adds required middlewares.
     */
    public final function __construct()
    {
        if (self::$configurations === null) {
            self::$configurations = [];

            if ($file = realpath(static::path('config', '.env'))) {
                $data = parse_ini_file($file, true);
                if (is_array($data)) {
                    self::$configurations = $data;
                }
            }
        }
    }

    protected static function createDefaultInstance(): static
    {
        static::verifyExtensionRequirements();
        return new static;
    }

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

    public static function env(string $key, mixed $default = null): mixed
    {
        return self::$configurations[$key] ?? $default;
    }

    /**
     * Retrieves the filesystem path for the extension.
     *
     * @param string ...$args Additional path segments to append.
     * @return string The filesystem path.
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
     * @return string The real filesystem path.
     */
    public static function realpath(string ...$args): string
    {
        return realpath(static::path(...$args));
    }

    /**
     * Factory method to retrieve instances from the extension's container or singletons.
     *
     * @param string $abstract The abstract service or class name.
     * @return mixed|null The resolved instance or null if not found.
     */
    public static function factory(string $abstract): mixed
    {
        if ($container = static::context()->container) {
            if ($container->isBound($abstract)) {
                return $container->make($abstract);
            }
        }

        if (is_subclass_of($abstract, SingletonInterface::class)) {
            return $abstract::getInstance();
        }

        return null;
    }

    /**
     * Performs additional mounting operations specific to the extension.
     *
     * @param Application $application
     */
    public static function mount(Application $application): void
    {
        $application->applyMiddleware(...self::REQUIRED_MIDDLEWARES);

        $container = $application->getServiceContainer();
        foreach (self::REGISTERED_SERVICES as $service => $concrete) {
            $container->bind($service, $concrete);
        }
    }

    public static function routes(string $prefix, array $options = null): ?Route
    {
        if ($appName = strtolower(App::name())) {
            $routes = "routes-$appName.php";
        } else {
            $routes = 'routes.php';
        }

        if ($file = realpath(static::path('config', $routes))) {
            return Route::group($prefix, fn() => include_once $file, $options);
        }

        return null;
    }
}
