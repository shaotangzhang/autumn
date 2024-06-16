<?php

/**
 * Autumn Framework App Class
 *
 * This class initializes and boots the Autumn framework application,
 * manages environment setup, application detection, and request handling.
 *
 * @package Autumn
 */

namespace Autumn;

use Autumn\System\Application;
use Autumn\System\Request;
use Autumn\System\Response;
use Composer\Autoload\ClassLoader;

defined('DOC_ROOT') or define('DOC_ROOT', dirname(__DIR__, 2));

/**
 * Autumn Framework App Class
 *
 * This class initializes and boots the Autumn framework application,
 * manages environment setup, application detection, and request handling.
 */
final class App
{
    /**
     * @var ClassLoader|null The Composer class loader instance.
     */
    private static ?ClassLoader $classLoader = null;

    /**
     * @var Application|null The current application instance.
     */
    private static ?Application $application = null;

    /**
     * Constructor.
     *
     * Sets up the basic environment including error reporting, loading environment variables,
     * setting timezone and locale, and including helper functions.
     */
    private function __construct()
    {
        ob_start();
        error_reporting(E_ALL | E_STRICT);
        set_error_handler('self::errorHandler');
        set_exception_handler('self::exceptionHandler');
        register_shutdown_function('self::fatalHandler');

        if (file_exists($file = DOC_ROOT . '/.env')) {
            $data = parse_ini_file($file, true);
            if (is_array($data)) {
                $_ENV = array_merge($_ENV, $data);
            }
        }

        date_default_timezone_set($_ENV['TIMEZONE'] ?? 'UTC');

        if ($locale = $_ENV['LOCALE'] ?? null) {
            setlocale(LC_ALL, $locale);
        }

        if ($file = realpath(__DIR__ . '/helper.php')) {
            include_once $file;
        }
    }

    /**
     * Get the current application instance.
     *
     * @return Application The current application instance.
     */
    public static function context(): Application
    {
        return self::$application ?? exit('No application is running.');
    }

    /**
     * Bootstraps the Autumn application.
     *
     * Starts the application by detecting the specified application,
     * executing its main function, creating an instance, and handling the request.
     *
     * @param string $appName The name of the application.
     * @param ClassLoader $classLoader The Composer class loader instance.
     * @return Response The response object.
     */
    public static function boot(string $appName, ClassLoader $classLoader): Response
    {
        if (self::$classLoader || self::$application) {
            exit('The application is already running.');
        }

        $class = self::detectApplication($appName);
        if (!$class) {
            exit(sprintf('The application `%s` is not found.', $appName));
        }

        self::$classLoader = $classLoader;
        $class::main(...$_SERVER['argv'] ?? []);

        self::$application = new $class($appName);
        return self::$application->handle(Request::capture());
    }

    /**
     * Detects if the specified application exists and is a subclass of Application.
     *
     * @param string $appName The name of the application.
     * @return string|null The fully qualified class name of the application if found and valid,
     *                     otherwise null.
     */
    private static function detectApplication(string $appName): ?string
    {
        // Default class name is the provided app name
        $class = $appName;

        // Check if the app name matches a valid pattern
        if (preg_match('/^[a-z]\w*(?:_[a-z]\w*)*$/i', $appName) && (strlen($appName) < 32)) {
            // Construct the expected file path based on conventional naming
            $file = implode(DIRECTORY_SEPARATOR, [DOC_ROOT, strtolower($appName), 'src', 'Application.php']);

            // If the file exists, construct the class name
            if (is_file($file)) {
                $class = str_replace(' ', '', ucwords(str_replace($appName, '_', ' '))) . '\\Application';
            }
        }

        // Check if the resolved class is a subclass of Application
        if (is_subclass_of($class, Application::class)) {
            return $class;
        }

        // If not found or not a valid subclass, return null
        return null;
    }

    /**
     * Maps the given path segments to a full path.
     *
     * @param string ...$args The path segments.
     * @return string The mapped full path.
     */
    public static function map(string ...$args): string
    {
        array_unshift($args, DOC_ROOT);
        return implode(DIRECTORY_SEPARATOR, $args);
    }

    /**
     * Resolves the real path of the given path segments.
     *
     * @param string ...$args The path segments.
     * @return string|null The resolved real path, or null if the path does not exist.
     */
    public static function realpath(string ...$args): ?string
    {
        return realpath(self::map(...$args)) ?: null;
    }

    /**
     * Exception handler function.
     *
     * Terminates the script and prints the exception message.
     *
     * @param \Throwable $exception The exception object.
     * @return void
     */
    public static function exceptionHandler(\Throwable $exception): void
    {
        if($_ENV['DEBUG']??null) {
            exit($exception);
        }

        exit('An error occurred.');
    }

    /**
     * Error handler function.
     *
     * Converts PHP errors into ErrorException instances and then calls exceptionHandler.
     *
     * @param int $error The error type.
     * @param string $message The error message.
     * @param string $file The filename where the error occurred.
     * @param int $line The line number where the error occurred.
     * @return void
     */
    public static function errorHandler(int $error, string $message, string $file, int $line): void
    {
        // Convert PHP errors into ErrorException instances
        self::exceptionHandler(new \ErrorException($message, $error, E_ERROR, $file, $line));
    }

    /**
     * Fatal error handler function.
     *
     * Handles fatal errors by calling errorHandler if a fatal error exists.
     *
     * @return void
     */
    public static function fatalHandler(): void
    {
        // Check if a fatal error occurred
        if ($error = error_get_last()) {
            self::errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }
}
