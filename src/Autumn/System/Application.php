<?php
namespace Autumn\System;

/**
 * Abstract base class for Autumn framework applications.
 *
 * Provides common functionality such as exception handling setup,
 * environment initialization, path mapping, and request handling.
 *
 * @package Autumn\System
 */
abstract class Application extends Container
{
    private static ?self $instance = null;

    private string $root;

    /**
     * @var array The array of service providers.
     */
    private array $providers = [];

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
        $this->root = dirname($reflection->getFileName());

        // Load environment configuration from .env file if exists
        if ($file = $this->realpath('config', '.env')) {
            $data = parse_ini_file($file);
            if (is_array($data)) {
                $_ENV = array_merge($_ENV, $data);
            }
        }
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
        if(!$route) {
            return new Response('Not Found', 404);
        }

        return $route->handle($request);
    }

    /**
     * Register a service provider.
     *
     * @param ServiceProvider $provider
     * @return void
     */
    public function registerProvider(ServiceProvider $provider): void
    {
        $provider->register($this);
        $this->providers[] = $provider;
    }
}
