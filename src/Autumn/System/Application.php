<?php

namespace Autumn\System;

use Autumn\Events\Event;
use Autumn\Exceptions\SystemException;
use Autumn\Http\Server\MiddlewareGroup;
use Autumn\Logging\ConsoleLogger;
use Autumn\System\Events\AppBootEvent;
use Autumn\System\ServiceContainer\ServiceContainer;
use Autumn\System\ServiceContainer\ServiceContainerInterface;
use Autumn\System\ServiceContainer\ServiceProviderInterface;
use Composer\Autoload\ClassLoader;
use JetBrains\PhpStorm\NoReturn;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Application
 *
 * Main application class that handles request and response, middleware, and service container.
 *
 * @package Autumn\System
 */
class Application implements RequestHandlerInterface
{
    /**
     * The single instance of the context
     *
     * @var Application|null
     */
    private static ?self $instance = null;

    /**
     * The root path of the application
     *
     * @var string
     */
    private string $root;

    /**
     * The registered extensions
     *
     * @var array
     */
    private array $registeredExtensions = [];

    /**
     * The registered middleware groups, a list of name-list pairs
     *
     * @var array
     */
    private array $registeredMiddlewareGroups = [];

    /**
     * The service container for the application
     *
     * @var ServiceContainerInterface
     */
    private ServiceContainerInterface $serviceContainer;

    /**
     * The active middleware handler
     *
     * @var MiddlewareGroup
     */
    private MiddlewareGroup $middlewareHandler;

    /**
     * The logger service
     *
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    protected array $serviceProviders = [];

    private static \Closure|false|null $bootOnce = null;

    /**
     * Application constructor.
     *
     * @param ClassLoader $classLoader Composer class loader instance.
     */
    public function __construct(private readonly ClassLoader $classLoader)
    {
        self::$instance ??= $this;

        set_error_handler([$this, 'errorHandler']);
        set_exception_handler([$this, 'exceptionHandler']);
        register_shutdown_function([$this, 'fatalHandler']);

        $this->serviceContainer = new ServiceContainer;
        $this->middlewareHandler = new MiddlewareGroup($this);

        $reflection = new \ReflectionObject($this);
        $this->root = dirname($reflection->getFileName(), 2);

        // load default configurations
        $this->loadConfig();

        // load the extensions
        $this->configExtensions();

        // load route settings
        $this->configRoutes();

        self::$bootOnce ??= function () {
            // muted the booting process
            self::$bootOnce = false;

            $this->serviceProviders = array_unique($this->serviceProviders);

            foreach ($this->serviceProviders as $index => $class) {
                if (is_subclass_of($class, ServiceProviderInterface::class)) {
                    $class::register($this->serviceContainer);
                } else {
                    unset($this->serviceProviders[$index]);
                }
            }

            foreach ($this->serviceProviders as $class) {
                $class::boot($this);
            }

            Event::dispatch(new AppBootEvent($this));
        };
    }

    /**
     * Entry point of the application.
     *
     * @param string ...$args Command line arguments.
     */
    public static function main(string ...$args): void
    {
        // Implement the entry point logic here
    }

    public final static function boot(ClassLoader $classLoader): static
    {
        self::$instance ??= new static($classLoader);

        if (self::$bootOnce) {
            call_user_func(self::$bootOnce);
            self::$bootOnce = false;
        }

        return self::$instance;
    }

    /**
     * Gets the application context.
     *
     * @return static
     * @throws \RuntimeException if the application context is not initialized yet.
     */
    public static function context(): static
    {
        if (!self::$instance) {
            throw new \RuntimeException('Application context is not initialized yet.');
        }

        return self::$instance;
    }

    /**
     * Loads configuration from the .env file.
     *
     * @return void
     */
    protected function loadConfig(): void
    {
        if (realpath($file = $this->path('config', '.env'))) {
            $data = parse_ini_file($file, true);
            if (is_array($data)) {
                $_ENV = array_merge($_ENV, $data);
            }
        }
    }

    /**
     * Configures extensions from the configuration file.
     *
     * @return void
     */
    protected function configExtensions(): void
    {
        if (realpath($file = $this->path('config', 'extensions.php'))) {
            $extensions = include_once $file;
            if (is_iterable($extensions)) {
                foreach ($extensions as $name => $extension) {
                    if (is_int($name)) {
                        if ($pos = strrpos($extension, '\\')) {
                            $name = strtolower(substr($name, $pos + 1));
                        } else {
                            $name = $extension;
                        }
                    }

                    if (!is_subclass_of($extension, Extension::class)) {
                        $extension = $this->getDefaultExtensionClass($name);
                    }

                    $this->registerExtension($name, $extension);
                }
            }
        }

        array_unshift($this->serviceProviders, ...array_values($this->registeredExtensions));
    }

    /**
     * Configures routes from the configuration file.
     *
     * @return void
     */
    protected function configRoutes(): void
    {
        if (realpath($file = $this->path('config', 'routes.php'))) {
            include_once $file;
        }
    }

    /**
     * Configures logger service
     *
     * @return void
     */
    protected function configLogger(): void
    {
        if ($this->serviceContainer->isBound(LoggerInterface::class)) {
            $this->logger = $this->serviceContainer->make(LoggerInterface::class);
        } else {
            $this->logger = new ConsoleLogger;
            $this->serviceContainer->bind(LoggerInterface::class, $this->logger);
        }
    }

    /**
     * Handles uncaught exceptions.
     *
     * @param \Throwable $exception The exception to handle.
     * @return void
     */
    #[NoReturn]
    public function exceptionHandler(\Throwable $exception): void
    {
        if (env('DEBUG')) {
            echo $exception;
        } else {
            echo $exception->getMessage();
        }
        exit;
    }

    /**
     * Handles PHP errors.
     *
     * @param int $error Error number.
     * @param string $message Error message.
     * @param string $file Filename where the error occurred.
     * @param int $line Line number where the error occurred.
     * @return void
     */
    #[NoReturn]
    public function errorHandler(int $error, string $message, string $file, int $line): void
    {
        $this->exceptionHandler(new \ErrorException($message, $error, E_ERROR, $file, $line));
    }

    /**
     * Handles fatal errors.
     *
     * @return void
     */
    public function fatalHandler(): void
    {
        if ($error = error_get_last()) {
            $this->errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * Gets the service container.
     *
     * @return ServiceContainerInterface
     */
    public function getServiceContainer(): ServiceContainerInterface
    {
        return $this->serviceContainer;
    }

    /**
     * Gets the root directory of the application.
     *
     * @return string
     */
    public function root(): string
    {
        return $this->root;
    }

    /**
     * Gets the full path by combining the root directory and given path segments.
     *
     * @param string ...$args Path segments.
     * @return string
     */
    public function path(string ...$args): string
    {
        array_unshift($args, $this->root);
        return implode(DIRECTORY_SEPARATOR, $args);
    }

    /**
     * Registers a namespace with an optional relative path.
     *
     * @param string $class Class name.
     * @param string|null $relativePath Relative path to the class file.
     * @return string
     * @throws \ReflectionException if the class cannot be reflected.
     */
    public function registerNamespace(string $class, string $relativePath = null): string
    {
        $reflection = new \ReflectionClass($class);
        $namespace = $reflection->getNamespaceName();
        $filePath = dirname($reflection->getFileName());
        if ($relativePath) {
            $filePath .= DIRECTORY_SEPARATOR . $relativePath;
        }
        if ($realpath = realpath($filePath)) {
            $this->classLoader->addPsr4($namespace . '\\', $realpath);
            return $realpath;
        }
        throw new \ReflectionException('Extension path `' . $filePath . '` is not found.');
    }

    /**
     * Registers an extension.
     *
     * @param string $name Extension name.
     * @param string $extension Extension class name or instance.
     * @return void
     */
    public function registerExtension(string $name, string $extension): void
    {
        if (isset($this->registeredExtensions[$name])) {
            throw SystemException::of('The extension name `%s` is used.', $name);
        }

        if (!is_subclass_of($extension, Extension::class)) {
            throw SystemException::of('Invalid extension type `%s`.', $extension);
        }

        if (in_array($extension, $this->registeredExtensions)) {
            return;
        }

        $this->registeredExtensions[$name] = $extension;
        $this->registerNamespace($extension);
    }

    /**
     * Handles the incoming server request and returns a response.
     *
     * @param ServerRequestInterface $request The server request.
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($route = Route::matches($request)) {
            return $route->handle($request);
        }

        return new Response('Not found', 404);
    }

    /**
     * Handles the incoming server request and returns a response after passing through the middleware.
     *
     * @param ServerRequestInterface $request The server request.
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request): ResponseInterface
    {
        return Route::guarded($request, $this->middlewareHandler);
    }

    /**
     * Sends the response after passing through the middleware.
     *
     * @return void
     */
    public function send(): void
    {
        $request = Request::capture();
        $response = $this->process($request);
        Response::fromResponseInterface($response)->send();
    }

    /**
     * Gets the middleware handler for the specified group.
     *
     * @param string $name Middleware group name.
     * @return array
     */
    public function getMiddlewareHandler(string $name): array
    {
        return $this->registeredMiddlewareGroups[$name] ?? [];
    }

    /**
     * Adds middlewares to a specified group.
     *
     * @param string $group Middleware group name.
     * @param string|MiddlewareInterface ...$middlewares Middleware instances or class names.
     * @return static
     */
    public function addToMiddlewareGroup(string $group, string|MiddlewareInterface ...$middlewares): static
    {
        if ($this->registeredMiddlewareGroups[$group] ??= []) {
            $diff = array_diff($middlewares, $this->registeredMiddlewareGroups[$group]);
            if ($diff) {
                array_push($this->registeredMiddlewareGroups[$group], ...$diff);
            }
        } else {
            $this->registeredMiddlewareGroups[$group] = array_unique($middlewares);
        }

        return $this;
    }

    /**
     * Applies middlewares from the specified groups.
     *
     * @param string ...$groups Middleware group names.
     * @return static
     */
    public function applyMiddlewareGroup(string ...$groups): static
    {
        foreach ($groups as $group) {
            if ($list = $this->registeredMiddlewareGroups[$group] ?? null) {
                $this->applyMiddleware(...$list);
            }
        }

        return $this;
    }

    /**
     * Applies the specified middlewares.
     *
     * @param string|MiddlewareInterface ...$middlewares Middleware instances or class names.
     * @return static
     */
    public function applyMiddleware(string|MiddlewareInterface ...$middlewares): static
    {
        ($this->middlewareHandler ??= new MiddlewareGroup($this))
            ->addMiddleware(...$middlewares);

        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getLanguageFile(string $domain, string $lang): string
    {
        $lang = $lang ?: $this->getDefaultLang();
        return $this->path('languages', $lang, strtr(trim($domain, '/\\'), '\\', '/'))
            . $this->getLanguageFileExt();
    }

    public function getLanguageFileExt(): string
    {
        return '.ini';
    }

    public function getDefaultLang(): string
    {
        return env('SITE_LANG', 'en');
    }

    public function getDefaultExtensionClass(string $name): string
    {
        static $cache;

        if (isset($this->registeredExtensions[$name])) {
            return $this->registeredExtensions[$name];
        }

        if (isset($cache[$name])) {
            return $cache[$name];
        }

        if (realpath($file = DOC_ROOT . '/src/extensions/' . $name . '/src/extension.php')) {
            $extension = include_once $file;
            if (is_string($extension)) {
                return $cache[$name] = $extension;
            }
        }

        return '';
    }
}
