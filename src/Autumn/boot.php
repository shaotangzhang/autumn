<?php

use Autumn\App;
use Autumn\Events\Event;
use Autumn\I18n\Locale;
use Autumn\System\Request;
use Autumn\System\Route;
use Autumn\System\Templates\TemplateService;
use Autumn\System\View;
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
     * Get the application instance or a specific service from the container.
     *
     * @param string|null $class The class name or service identifier.
     * @param bool|string|callable|null $concrete The concrete implementation or binding callback.
     * @return mixed The application instance or the requested service.
     */
    function app(string $class = null, bool|string|callable $concrete = null): mixed
    {
        static $app;
        if ($app === null) {
            $app = App::context();

            if (!$app) {
                if (!preg_match('/^\w+$/', $class ??= 'app')) {
                    exit('Invalid application name: ' . $class);
                }

                return $app = App::boot($class, require_once DOC_ROOT . '/vendor/autoload.php');
            }
        }

        if (!is_string($class) || ($class = trim($class))) {
            return $app;
        }

        static $container;
        $container ??= $app->getServiceContainer();

        if ($concrete) {
            if (!$container->isBound($class)) {
                $container->bind($class, ($concrete === true) ? $class : $concrete);
            }
        }

        return $container->make($class);
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

if (!function_exists('fire')) {
    /**
     * Fire an event with the given arguments.
     *
     * @param string $event The name of the event to fire.
     * @param object|array|null $sender The event sender or an array of arguments.
     * @param array|null $args Additional arguments to pass to the event handlers.
     * @return bool True if the event was handled successfully, false otherwise.
     */
    function fire(string $event, object|array $sender = null, array $args = null): bool
    {
        if (is_array($sender)) {
            return Event::fire($event, null, array_merge($sender, $args ?? []));
        }

        return Event::fire($event, $sender, $args);
    }
}

if (!function_exists('hook')) {
    /**
     * Register an event handler for a specific event.
     *
     * @param string $event The name of the event to listen for.
     * @param callable $handler The event handler to register.
     * @return void
     */
    function hook(string $event, callable $handler): void
    {
        Event::listen($event, $handler);
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
     */
    function translate(string $text, array $args = null, string $domain = null): string
    {
        return Locale::translate($text, $args, $domain);
    }
}

if (!function_exists('action')) {
    /**
     * Process a route and return the response.
     *
     * @param string $route The route path to process.
     * @param array|ArrayAccess|null $args The route parameters or request object.
     * @param array|null $context The request context, such as the HTTP method.
     * @return ResponseInterface|null The response object, or null if no route matches.
     */
    function action(string $route, array|ArrayAccess $args = null, array $context = null): ?ResponseInterface
    {
        if ($args instanceof ServerRequestInterface) {
            $request = $args;
        } else {
            $request = Request::context();
            if ($args) {
                $request = $request->withQueryParams($args);
            }
        }

        $request = $request->withMethod($context['method'] ?? 'GET')
            ->withUri($request->getUri()->withPath($route));

        return Route::matches($request)?->handle($request);
    }
}

if (!function_exists('view')) {
    /**
     * Render a specified view template.
     *
     * @param string $name The name of the view template.
     * @param array|ArrayAccess|null $args The parameters to pass to the view template.
     * @param array|null $context The context for rendering the view.
     * @return void
     */
    function view(string $name, array|ArrayAccess $args = null, array $context = null): void
    {
        $view = new View($name, $args, $context);
        TemplateService::context()->renderView($view);
    }
}

