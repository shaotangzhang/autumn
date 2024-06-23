<?php

namespace Autumn\System;

use Composer\Autoload\ClassLoader;
use JetBrains\PhpStorm\NoReturn;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Application implements RequestHandlerInterface
{
    private static ?self $instance = null;

    private string $root;

    private ?RequestHandlerInterface $handler;

    public function __construct(private readonly ClassLoader $classLoader)
    {
        self::$instance ??= $this;

        $reflection = new \ReflectionObject($this);
        $this->root = dirname($reflection->getFileName());

        set_error_handler('self::errorHandler');
        set_exception_handler('self::exceptionHandler');
        register_shutdown_function('self::fatalHandler');

        if (realpath($file = $this->path('config', '.env'))) {
            $data = parse_ini_file($file, true);
            if (is_array($data)) {
                $_ENV = array_merge($_ENV, $data);
            }
        }
    }

    public static function main(string ...$args): void
    {

    }

    public static function context(): static
    {
        if (!self::$instance) {
            throw new \RuntimeException('Application context is not initialized yet.');
        }

        return self::$instance;
    }

    #[NoReturn]
    public static function exceptionHandler(\Throwable $exception): void
    {
        echo $exception->getMessage();
        exit;
    }

    #[NoReturn]
    public static function errorHandler(int $error, string $message, string $file, int $line): void
    {
        self::exceptionHandler(new \ErrorException($message, $error, E_ERROR, $file, $line));
    }

    public static function fatalHandler(): void
    {
        if ($error = error_get_last()) {
            self::errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * @return string
     */
    public function root(): string
    {
        return $this->root;
    }

    public function path(string ...$args): string
    {
        array_unshift($args, $this->root);
        return implode(DIRECTORY_SEPARATOR, $args);
    }

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

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($route = Route::matches($request)) {
            return $route->handle($request);
        }

        return new Response('Not found', 404);
    }
}
