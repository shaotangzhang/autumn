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

    private static array $instances = [];
    private static array $extensionRoots = [];

    private ?ServiceContainerInterface $container = null;

    /**
     * Extension constructor.
     *
     * Registers the extension with the application and adds required middlewares.
     */
    public final function __construct()
    {
        if (!isset(static::$instances[static::class])) {
            static::$instances[static::class] = $this;

            $class = '\\' . static::class;
            $namespace = substr($class, 0, strrpos($class, '\\'));
            App::registerNamespacePath($namespace, static::path('src'));

            if ($config = static::realpath('config.php')) {
                include_once $config;
            }
        }
    }

    /**
     * Retrieves the singleton instance of the extension.
     *
     * @return static
     */
    public static final function context(): static
    {
        if (!isset(self::$instances[static::class])) {
            static::verifyExtensionRequirements();
            self::$instances[static::class] = new static;
        }

        return self::$instances[static::class];
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
     * Registers the extension with the provided service container.
     *
     * @param ServiceContainerInterface $container The service container instance.
     */
    public function register(ServiceContainerInterface $container): void
    {
//        $this->verifyExtensionRequirements();

        $this->container = $container;

        // Context for dependency injection
        $context = [
            self::class => $this,
            static::class => $this
        ];

        // Bind registered services to the container
        foreach (static::REGISTERED_SERVICES as $abstract => $service) {
            $container->bind($abstract, $service, $context);
        }

        // Additional extension mounting logic
        $this->mount($container);
    }

    /**
     * Performs additional mounting operations specific to the extension.
     *
     * @param ServiceContainerInterface $container The service container instance.
     */
    public function mount(ServiceContainerInterface $container): void
    {
        // Additional logic for mounting entities or performing setup
        // This method can be overridden in subclasses for extension-specific behavior
    }
}
