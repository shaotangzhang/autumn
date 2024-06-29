<?php

namespace Console;

defined('PHP_CLI') or define('PHP_CLI', true);
defined('PHP_CLI_MODE') or define('PHP_CLI_MODE', PHP_CLI);
defined('PHP_CLI_VERSION') or define('PHP_CLI_VERSION', PHP_VERSION);
defined('PHP_CLI_RELEASE') or define('PHP_CLI_RELEASE', PHP_VERSION);

class Application extends \Autumn\System\Application
{
    public const VERSION = '1.0.0';

    public static function main(string ...$args): void
    {
        $first = reset($args);
        if (is_file($first)) {
            array_shift($args);
        }

        if (!str_starts_with($args[0] ?? '', '-')) {
            $_SERVER['REQUEST_METHOD'] = $args[0];
            array_shift($args);
        } else {
            $_SERVER['REQUEST_METHOD'] ??= 'GET';
        }

        $params = static::parseParams($args);
        static::handleCommand($params);
        array_shift($params);
        $_GET['args'] = $params;
        parent::main();
    }

    private static function parseParams(array $args): array
    {
        $params = [];
        $paramName = null;

        foreach ($args as $arg) {
            if (str_starts_with($arg, '-')) {
                $paramName = ltrim($arg, '-');
                $params[$paramName] = true;
            } else {
                if ($paramName) {
                    $params[$paramName] = $arg;
                    $paramName = null;
                } else {
                    $params[] = $arg;
                }
            }
        }

        return $params;
    }

    private static function handleCommand(array $params): void
    {
        $method = 'command_' . strtolower($_SERVER['REQUEST_METHOD'] ?? '');
        if (method_exists(__CLASS__, $method)) {
            static::$method($params);
        } else {
            static::command_help([]);
        }
    }

    private static function handleRequest(array $params): void
    {
        $_SERVER['REQUEST_METHOD'] = strtoupper($_SERVER['REQUEST_METHOD']);
        $_SERVER['REQUEST_URI'] = '/' . ltrim($params[0] ?? '', '/');
    }

    private static function command_get(array $params): void
    {
        static::handleRequest($params);
    }

    private static function command_post(array $params): void
    {
        static::handleRequest($params);
    }

    private static function command_put(array $params): void
    {
        static::handleRequest($params);
    }

    private static function command_patch(array $params): void
    {
        static::handleRequest($params);
    }

    private static function command_head(array $params): void
    {
        static::handleRequest($params);
    }

    private static function command_delete(array $params): void
    {
        static::handleRequest($params);
    }

    private static function command_trace(array $params): void
    {
        static::handleRequest($params);
    }

    private static function command_connect(array $params): void
    {
        static::handleRequest($params);
    }

    private static function command_options(array $params): void
    {
        static::handleRequest($params);
    }

    private static function command_v(array $params): void
    {
        static::command_version($params);
    }

    private static function command_version(array $params): void
    {
        exit(static::VERSION . PHP_EOL);
    }

    private static function command_h(array $params): void
    {
        static::command_help($params);
    }

    private static function command_help(array $params): void
    {
        $helpText = <<<EOT
Usage: php index.php [METHOD] [URI] [OPTIONS]

Options:
    -h, --help           Display this help message
    -v, --version        Display the application version

Methods:
    GET                  Retrieve data from the server
    POST                 Submit data to the server
    PUT                  Update data on the server
    PATCH                Partially update data on the server
    DELETE               Delete data from the server
    HEAD                 Retrieve headers from the server
    OPTIONS              Retrieve available HTTP methods from the server
    TRACE                Perform a message loop test on the server
    CONNECT              Establish a tunnel to the server
EOT;
        exit($helpText . PHP_EOL);
    }

    public function exceptionHandler(\Throwable $exception): void
    {
        print_r($exception);
        exit;
    }
}
