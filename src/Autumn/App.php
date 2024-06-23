<?php

namespace Autumn;

use Autumn\System\Application;
use Composer\Autoload\ClassLoader;

defined('DOC_ROOT') or define('DOC_ROOT', dirname(__DIR__, 2));

final class App
{
    private static ?self $instance = null;
    private static ?ClassLoader $classLoader = null;
    private static ?Application $application = null;

    private function __construct(private readonly string $appName)
    {
        self::$instance = $this;

        ob_start();
        error_reporting(E_ALL | E_STRICT);

        if ($file = self::realpath('.env')) {
            $data = parse_ini_file($file, true);
            if (is_array($data)) {
                $_ENV = array_merge($_ENV, $data);
            }
        }

        $timezone = ($_ENV['TIMEZONE'] ?? null) ?: 'utc';
        date_default_timezone_set($timezone);

        if ($locale = $_ENV['LOCALE'] ?? null) {
            if (function_exists('setlocale')) {
                setlocale(LC_ALL, $locale);
            }
        }
    }

    public static function context(): ?Application
    {
        return self::$application;
    }

    public static function boot(string $appName, ClassLoader $classLoader)
    {
        if (self::$classLoader || self::$application) {
            exit('The application is already boot.');
        }

        $file = self::realpath($appName, 'src', 'Application.php');
        if (!is_file($file)) {
            exit('The application `' . $appName . '` is not found.');
        }
        require_once $file;

        $class = ucfirst($appName) . '\\Application';
        if (!is_subclass_of($class, Application::class)) {
            exit('The application `' . $appName . '` is not found.');
        }

        self::$classLoader = $classLoader;
        $classLoader->addPsr4(ucfirst($appName) . '\\', dirname($file));

        new self($appName);

        $class::main(...$_SERVER['argv'] ?? []);
        return self::$application = new $class($classLoader);
    }

    public static function name(): string
    {
        return self::$instance?->appName ?? '';
    }

    public static function map(string ...$args): string
    {
        array_unshift($args, DOC_ROOT);
        return implode(DIRECTORY_SEPARATOR, $args);
    }

    public static function realpath(string ...$args): string
    {
        return realpath(self::map(...$args));
    }
}