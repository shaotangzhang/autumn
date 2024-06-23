<?php

namespace Autumn\System;

use Autumn\App;
use Autumn\Http\Server\MiddlewareGroup;
use Autumn\System\ServiceContainer\ServiceContainer;
use Autumn\System\ServiceContainer\ServiceContainerInterface;
use Autumn\System\ServiceContainer\ServiceProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Abstract base class for Autumn framework applications.
 *
 * Provides common functionality such as exception handling setup,
 * environment initialization, path mapping, and request handling.
 *
 * @package Autumn\System
 */
abstract class Application
{
    private static ?self $instance = null;
    private ServiceContainerInterface $serviceContainer;
    private string $root;

    /**
     * @var array The array of service providers.
     */
    private array $registeredProviders = [];

    private MiddlewareGroup|null $middlewareGroup = null;

    /**
     * Constructor.
     *
     * Initializes the application instance, sets up exception handling,
     * and loads environment configuration.
     */
    public function __construct()
    {
        // Set exception handler if defined in the subclass
        if (!self::$instance && method_exists($this, 'exceptionHandler')) {
            set_exception_handler([$this, 'exceptionHandler']);
        }

        // Store the current instance statically
        self::$instance = $this;

        // Determine root directory of the application
        $reflection = new \ReflectionObject($this);
        $this->root = dirname($reflection->getFileName(), 2);

        // Load environment configuration from .env file if exists
        if ($file = $this->realpath('config', '.env')) {
            $data = parse_ini_file($file);
            if (is_array($data)) {
                $_ENV = array_merge($_ENV, $data);
            }
        }

        $this->loadExtensions();
        $this->loadRoutes();
    }

    /**
     * @return MiddlewareGroup
     */
    public function getMiddlewareGroup(): MiddlewareGroup
    {
        return $this->middlewareGroup ??= new MiddlewareGroup;
    }

    protected function loadRoutes(): void
    {
        if ($file = $this->realpath('config', 'routes.php')) {
            include_once $file;
        }
    }

    protected function loadExtensions(): void
    {
        if ($file = $this->realpath('config', 'extensions.php')) {
            $extensions = include_once $file;
            if (is_array($extensions)) {
                $group = $this->getMiddlewareGroup();

                foreach ($extensions as $alias => $extension) {
                    if ($extension instanceof Extension) {
                        // Register the extension as a service provider within the application
                        $this->registerProvider($extension, is_string($alias) ? $alias : null);

                        // Add REQUIRED_MIDDLEWARES of the extension to the application
                        $group->addMiddleware(...$extension::REQUIRED_MIDDLEWARES);
                    }
                }
            }
        }
    }

    /**
     * @return ServiceContainerInterface
     */
    public function getServiceContainer(): ServiceContainerInterface
    {
        return $this->serviceContainer ??= new ServiceContainer;
    }

    /**
     * Register a service provider.
     *
     * @param ServiceProviderInterface $provider The service provider instance to register.
     * @param string|null $alias Optional alias for the service provider.
     * @throws \RuntimeException If the service provider or alias is already registered.
     */
    public function registerProvider(ServiceProviderInterface $provider, string $alias = null): void
    {
        // Check if alias is provided and validate against existing providers
        if ($alias) {
            if (isset($this->registeredProviders[$alias])) {
                throw new \RuntimeException(sprintf("The service provider alias `%s` is already registered.", $alias));
            }
        }

        // Check if the provider instance is already registered
        if (in_array($provider, $this->registeredProviders, true)) {
            throw new \RuntimeException(sprintf("The service provider `%s` is already registered.", $provider::class));
        }

        // Register the provider with the service container
        $provider->register($this->getServiceContainer());

        // Store the provider instance with its alias if provided
        if ($alias) {
            $this->registeredProviders[$alias] = $provider;
        }
    }

    /**
     * Get a registered service provider by its alias or class name.
     *
     * @param string $provider The alias or class name of the service provider.
     * @return ServiceProviderInterface|null The registered service provider instance, or null if not found.
     */
    public function getRegisteredProvider(string $provider): ?ServiceProviderInterface
    {
        // Check if the provider alias exists directly
        if (isset($this->registeredProviders[$provider])) {
            return $this->registeredProviders[$provider];
        }

        // Check if the provider class name exists in the providers array
        foreach ($this->registeredProviders as $registeredProvider) {
            if (get_class($registeredProvider) === $provider) {
                return $registeredProvider;
            }
        }

        // Return null if provider not found
        return null;
    }

    /**
     * Main entry point for the application.
     *
     * This method should be implemented in the subclass
     * to define the main logic of the application.
     *
     * @param string ...$args Command line arguments passed to the application.
     * @return void
     */
    public static function main(string ...$args): void
    {
        // Implement logic in subclasses
    }

    /**
     * Get the root directory of the application.
     *
     * @return string The root directory path.
     */
    public function root(): string
    {
        return $this->root;
    }

    /**
     * Map the given path segments relative to the application's root directory.
     *
     * @param string ...$args Path segments.
     * @return string The mapped full path.
     */
    public function map(string ...$args): string
    {
        array_unshift($args, $this->root());
        return implode(DIRECTORY_SEPARATOR, $args);
    }

    /**
     * Resolve the real path of the given path segments relative to the application's root directory.
     *
     * @param string ...$args Path segments.
     * @return string|null The resolved real path, or null if the path does not exist.
     */
    public function realpath(string ...$args): ?string
    {
        return realpath($this->map(...$args)) ?: null;
    }

    /**
     * Handle the incoming request and return a response.
     *
     * This method should be overridden in the subclass to implement
     * request handling logic specific to the application.
     *
     * @param Request $request The incoming request object.
     * @return Response The response object.
     */
    public function handle(Request $request): Response
    {
        $route = Route::matches($request);
        if (!$route) {
            return new Response('Not Found', 404);
        }

        return Response::fromResponseInterface($route->process($request));
    }
}
