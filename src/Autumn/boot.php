<?php

use Autumn\App;
use Autumn\Database\Interfaces\SiteInterface;
use Autumn\Events\Event;
use Autumn\I18n\Locale;
use Autumn\I18n\Translatable;
use Autumn\I18n\Translation;
use Autumn\System\Application;
use Autumn\System\Request;
use Autumn\System\Route;
use Autumn\System\Templates\Component;
use Autumn\System\Templates\TemplateService;
use Autumn\System\View;
use Composer\Autoload\ClassLoader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

require_once __DIR__ . '/App.php';

if (!function_exists('env')) {
    /**
     * Get the value of an environment variable.
     *
     * @param string $key The environment variable key.
     * @param mixed $default The default value to return if the environment variable is not set.
     * @return mixed The value of the environment variable, or the default value.
     */
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $default;
    }
}


if (!function_exists('app')) {
    /**
     * Retrieves or bootstraps the application instance.
     *
     * @param string $appName The name of the application.
     * @param ClassLoader|null $classLoader The class loader to use.
     * @return Application|null The application instance.
     */
    function app(string $appName = 'app', ClassLoader $classLoader = null): ?Application
    {
        static $app;

        return $app ??= App::context()
            ?? App::boot($appName, $classLoader ?? require_once DOC_ROOT . '/vendor/autoload.php');
    }
}

if (!function_exists('make')) {
    /**
     * Creates an instance of the specified class.
     *
     * @param string $class The class to instantiate.
     * @param bool|string|callable|null $concrete The concrete implementation or a factory.
     * @param bool $silent Whether to suppress exceptions.
     * @return mixed The created instance.
     */
    function make(string $class, bool|string|callable $concrete = null, bool $silent = false): mixed
    {
        static $container;
        $container ??= app()?->getServiceContainer();

        if (!$container) {
            exit('The application is not ready yet.');
        }

        if ($concrete) {
            if (!$container->isBound($class)) {
                $container->bind($class, ($concrete === true) ? $class : $concrete);
            }
        }

        if ($silent) {
            return $container->factory($class);
        } else {
            return $container->make($class);
        }
    }
}

if (!function_exists('call')) {
    /**
     * Invoke a callable with the provided arguments and context.
     *
     * @param callable $func The callable to invoke.
     * @param array|ArrayAccess|null $args The arguments to pass to the callable.
     * @param array|null $context The context for resolving dependencies.
     * @param object|null $scope The object scope to bind the callable to.
     * @return mixed The result of the callable execution.
     */
    function call(callable $func, array|ArrayAccess $args = null, array $context = null, object $scope = null): mixed
    {
        if ($scope) {
            $func = $func(...)->bindTo($scope);
        }

        return app()->getServiceContainer()->invoke($func, $args, $context);
    }
}

if (!function_exists('translate')) {
    /**
     * Translate a given text string using the localization system.
     *
     * @param string $text The text string to translate.
     * @param array|null $args Optional arguments to replace placeholders in the text.
     * @param string|null $domain The translation domain to use.
     * @return string The translated text string.
     *
     * @deprecated
     */
    function translate(string $text, array $args = null, string $domain = null): string
    {
        return Locale::translate($text, $args, $domain);
    }
}

if (!function_exists('t')) {
    /**
     * Retrieve the translated text for a given key or return the default value.
     *
     * This function attempts to get the translation of the given text key from the global
     * translation system. If the translation is not found, it returns the provided default value.
     *
     * @param string|null $text The text key to be translated.
     * @param mixed $default The default value to return if the translation is not found.
     * @param string|null $lang The language code for the translation (optional).
     * @return string|null The translated text or the default value if no translation is found.
     */
    function t(?string $text, mixed $default = null, string $lang = null): ?string
    {
        return Translation::global()?->translate($text, [], $lang) ?? $default;
    }
}

if (!function_exists('tt')) {
    /**
     * Retrieve the translated text for a given key.
     *
     * @param string|null $text The text key to be translated.
     * @param mixed ...$args
     * @return string|null The translated text or the default value if no translation is found.
     */
    function tt(?string $text, mixed ...$args): ?string
    {
        return Translation::global()?->format($text, ...$args);
    }
}

if (!function_exists('t_text')) {
    /**
     * Translate the given text with arguments.
     *
     * @param string|null $text The key for the translation text.
     * @param mixed ...$args The arguments to replace placeholders in the translation text.
     * @return string|null The translated text with arguments applied.
     */
    function t_text(?string $text, mixed ...$args): ?string
    {
        return Translation::global()?->translate($text, $args);
    }
}

if (!function_exists('t_html')) {
    /**
     * Translate the given text with arguments and escape it for HTML output.
     *
     * @param string|null $text The key for the translation text.
     * @param mixed ...$args The arguments to replace placeholders in the translation text.
     * @return string|null The translated and HTML-escaped text.
     */
    function t_html(?string $text, mixed ...$args): ?string
    {
        if ($html = tt($text, ...$args)) {
            return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
        }
        return null;
    }
}

if (!function_exists('t_attr')) {
    /**
     * Translate the given text with arguments and escape it for use in HTML attributes.
     *
     * @param string|null $text The key for the translation text.
     * @param mixed ...$args The arguments to replace placeholders in the translation text.
     * @return string|null The translated and attribute-escaped text.
     */
    function t_attr(?string $text, mixed ...$args): ?string
    {
        if ($attr = tt($text, ...$args)) {
            return htmlspecialchars($attr, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        return null;
    }
}

if (!function_exists('component')) {
    /**
     * Creates an instance of a component
     *
     * @param string $name
     * @param array|null $attributes
     * @param array|null $context
     * @param mixed ...$children
     * @return Component|null
     */
    function component(string $name, array $attributes = null, array $context = null, mixed ...$children): ?Component
    {
        $viewName = strtr($name, ['\\' => '/', '.' => '/', '::' => '/', ':' => '/', '//' => '/']);
        $view = new View('/component/' . $viewName);

        $component = new Component($context['tagName'] ?? strtr($name, '/', '-'), $attributes, $context, ...$children);
        $component->setView($view);
        return $component;
    }
}

if (!function_exists('html')) {
    /**
     * Escapes HTML content to make it safe for output.
     *
     * @param string|null $html The HTML content to escape.
     * @return string|null The escaped HTML content, or null if input is null.
     */
    function html(string $html = null): ?string
    {
        if (is_string($html)) {
            return htmlspecialchars($html, ENT_COMPAT, 'UTF-8');
        }
        return $html;
    }
}

if (!function_exists('attr')) {
    /**
     * Escapes attribute values to make them safe for output in HTML attributes.
     *
     * @param string|null $text The attribute value to escape.
     * @return string|null The escaped attribute value, or null if input is null.
     */
    function attr(string $text = null): ?string
    {
        if (is_string($text)) {
            return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        return $text;
    }
}

if (!function_exists('url')) {
    /**
     * Generates a URL with optional query parameters and fragment.
     *
     * @param string|null $path The base path of the URL.
     * @param array|null $args The query parameters to append to the URL.
     * @param string|null $fragment The fragment to append to the URL.
     * @return string|null The generated URL, or null if path is null.
     */
    function url(?string $path = null, ?array $args = null, ?string $fragment = null): ?string
    {
        if ($args) {
            $path .= '?' . http_build_query($args);
        }

        if ($fragment) {
            $path .= '#' . rawurlencode($fragment);
        }

        return $path;
    }
}
