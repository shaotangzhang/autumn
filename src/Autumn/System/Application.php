<?php

namespace Autumn\System;

use Autumn\Events\Event;
use Autumn\Exceptions\SystemException;
use Autumn\Http\Server\MiddlewareGroup;
use Autumn\Logging\ConsoleLogger;
use Autumn\System\Events\AppBootEvent;
use Autumn\System\ServiceContainer\ServiceContainer;
use Autumn\System\ServiceContainer\ServiceContainerInterface;
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

        $this->loadConfig();
        $this->configLogger();
        $this->configExtensions();
        $this->configImplements();
        $this->configMiddlewares();
        $this->configRoutes();

        Event::listen(AppBootEvent::class, function (AppBootEvent $event) {
            if ($event->getApplication() === $this) {
                $this->boot();
            }
        });
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

    protected function boot(): void
    {
        $this->getLogger()->info('Application starts');
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
     * Configures middlewares from the configuration file.
     *
     * @return void
     */
    protected function configMiddlewares(): void
    {
        if (realpath($file = $this->path('config', 'middlewares.php'))) {
            $middlewares = include_once $file;
            if (is_iterable($middlewares)) {
                foreach ($middlewares as $group => $middleware) {
                    if (is_array($middleware)) {
                        $this->addToMiddlewareGroup($group, ...$middleware);
                    } elseif (is_subclass_of($middleware, MiddlewareInterface::class)) {
                        $this->applyMiddleware($middleware);
                    }
                }
            }
        }
    }

    /**
     * Configures implementations from the configuration file.
     *
     * @return void
     */
    protected function configImplements(): void
    {
        if (realpath($file = $this->path('config', 'implements.php'))) {
            $services = include_once $file;
            if (is_iterable($services)) {
                foreach ($services as $abstract => $concrete) {
                    $this->serviceContainer->bind($abstract, $concrete);
                }
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
                    $this->registerExtension($name, $extension);
                }
            }
        }
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
     * @throws \RuntimeException if the class cannot be reflected.
     */
    public function registerNamespace(string $class, string $relativePath = null): string
    {
        try {
            $reflection = new \ReflectionClass($class);
            $namespace = $reflection->getNamespaceName();
            $filePath = realpath(dirname($class) . DIRECTORY_SEPARATOR . $relativePath);
            $this->classLoader->addPsr4($namespace . '\\', $filePath);
            return $filePath;
        } catch (\ReflectionException $ex) {
            throw new \RuntimeException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    /**
     * Registers an extension.
     *
     * @param string $name Extension name.
     * @param string|Extension $extension Extension class name or instance.
     * @return void
     * @throws SystemException if the extension is invalid or already registered.
     */
    public function registerExtension(string $name, string|Extension $extension): void
    {
        $extensionClass = is_string($extension) ? $extension : $extension::class;

        if (isset($this->registeredExtensions[$name])) {
            throw SystemException::of('The extension name `%s` is used.', $name);
        }

        if (in_array($extensionClass, $this->registeredExtensions)) {
            throw SystemException::of('The extension `%s` is already registered.', $name);
        }

        if (!is_subclass_of($extension, Extension::class)) {
            throw SystemException::of('Invalid extension type `%s`.', $extensionClass);
        }

        $this->registerNamespace($extensionClass);
        $extension::mount($this);
        $this->registeredExtensions[$name] = $extensionClass;
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
        return $this->middlewareHandler->handle($request);
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
}
