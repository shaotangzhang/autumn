<?php

namespace Autumn\System;

use Autumn\Database\Interfaces\EntityManagerInterface;
use Autumn\Exceptions\ForbiddenException;
use Autumn\Http\Server\MiddlewareGroup;
use Autumn\Interfaces\ModelInterface;
use Autumn\System\Requests\BaseRequest;
use Autumn\System\Responses\ResponseService;
use Autumn\System\ServiceContainer\ParameterResolver;
use Autumn\System\ServiceContainer\ParameterResolverInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Route implements RequestHandlerInterface
{

    private static array $fallbacks = [];

    private static ?self $currentScope = null;

    private static array $acceptableMethods = ['GET', 'POST'];

    private static array $placeholders = [
        'id' => '\d+',       // 匹配数字，至少一位
        'slug' => '[a-zA-Z0-9-]+(/[a-zA-Z0-9-]+)*',  // 匹配字母、数字和破折号（减号）
        'uuid' => '[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}', // 匹配 UUID
        'name' => '[a-zA-Z]\w*', // 匹配至少一个字母
        'date' => '(0[1-9]|[12][0-9]|3[01])', // 匹配日期（01-31）
        'month' => '(0[1-9]|1[0-2])', // 匹配月份（01-12）
        'year' => '(19|20)\d{2}', // 匹配年份（1900-2099）
        'word' => '\w+',       // 匹配单词字符（字母、数字、下划线）
        'alpha' => '[a-zA-Z]+', // 匹配至少一个字母（大小写）
    ];

    private string $modifiers = '';

//    private iterable $middlewares = [];

    /**
     * @var array<string, array<string, self>>
     */
    private static array $rules = [];
    /**
     * @var array<string, self>
     */
    private static array $profiles = [];
    private ?self $group = null;
    private array $variables = [];
    private mixed $handler = null;

    private ?MiddlewareGroup $middlewareGroup = null;

    public function __construct(private readonly string      $path,
                                private string|\Closure|null $callback = null,
                                array                        $options = null
    )
    {
        foreach ($options ?? [] as $name => $value) {
            if (method_exists($this, $func = 'set' . $name)) {
                $this->$func($value);
            }
        }
    }

    public static function location(string $profile, array $args = null): string
    {
        $name = trim($profile, '/\\');
        if ($route = self::$profiles[$name] ?? null) {
            return $route->buildUrl($args);
        }
        return $name;
    }

    public function withVariables(array $variables): static
    {
        if ($this->variables === $variables) {
            return $this;
        }

        $clone = clone $this;
        $clone->variables = $variables;
        return $clone;
    }

    public static function all(): array
    {
        return self::$rules;
    }

    /**
     * Parses a route pattern into a regular expression.
     *
     * @param string $format The route pattern format.
     * @param string|null $modifiers Optional modifiers to append to the regex pattern.
     * @return string The generated regular expression.
     */
    public static function parsePattern(string $format, string $modifiers = null): string
    {
        if ($format === '') {
            return '';
        }

        // Replace wildcard /* with a regex pattern for matching one or more segments
        $pattern = str_replace('/*', '(/[^/]+)', $format);

        // Replace placeholders like {placeholder} with corresponding regex patterns
        $pattern = preg_replace_callback('/{\s*([a-zA-Z0-9_]+)\s*(?:#(.+)#)?}/', [self::class, 'placeholder'], $pattern);

        // Enclose the final pattern in regex delimiters and append modifiers if specified
        return "#^$pattern$#" . $modifiers;
    }

    /**
     * Generates a regex pattern for a given route placeholder.
     *
     * @param array $matches An array of regex matches, where $matches[1] is the placeholder name.
     * @return string The regex pattern for the placeholder.
     */
    public static function placeholder(array $matches): string
    {
        if ($placeholderName = $matches[1] ?? null) {
            if ($placeholderPattern = $matches[2] ?? null) {
                return "(?P<$placeholderName>$placeholderPattern)";
            } elseif ($pattern = self::$placeholders[$placeholderName] ?? null) {
                return "(?P<$placeholderName>$pattern)";
            }
        }

        return '';
    }

    /**
     * Defines a regex pattern for a specific route placeholder.
     *
     * @param string $placeholder The name of the placeholder.
     * @param string|null $pattern The regex pattern to associate with the placeholder.
     *                             If null, the placeholder will be removed from the list of patterns.
     * @return void
     */
    public static function when(string $placeholder, string $pattern = null): void
    {
        if ($pattern !== null) {
            self::$placeholders[$placeholder] = $pattern;
        } else {
            unset(self::$placeholders[$placeholder]);
        }
    }

    public static function methods(string ...$methods): void
    {
        self::$acceptableMethods = $methods;
    }

    public static function create(string $path, string|callable $handler, array $options = null): static
    {
        return new static($path, $handler, $options);
    }

    public static function addRule(string|array $methods, string $path, string|callable $handler, array $options = null): static
    {
        if (is_string($methods)) {
            $methods = [$methods];
        }

        $options['group'] ??= self::$currentScope;
        $route = self::create($path, $handler, $options);

        $groupPath = self::$currentScope?->getPath();

        foreach ($methods as $method) {
            $method = strtoupper($method);
            if (in_array($method, self::$acceptableMethods)) {
                self::$rules[$method][$groupPath . $path] = $route;
            }
        }

        return $route;
    }

    public static function admin(string $path, string $handler, array $options = null): static
    {
        return static::group(self::$currentScope?->getPath() . $path, function () use ($options, $handler) {
            static::get('/index/{id}', $handler . '@show', $options);
            static::get('/index', $handler, $options);

            static::get('/create', $handler . '@add', $options);
            static::post('/create', $handler . '@create', $options);

            static::get('/update(/{id})?', $handler . '@edit', $options);
            static::post('/update(/{id})?', $handler . '@update', $options);

            static::get('/delete(/{id})?', $handler . '@delete', $options);
            static::post('/delete(/{id})?', $handler . '@destroy', $options);

            static::get('/trash(/{id})?', $handler . '@trash', $options);
            static::get('/restore(/{id})?', $handler . '@restore', $options);
        });
    }

    public static function get(string $path, string|callable $handler, array $options = null): static
    {
        return static::addRule(__FUNCTION__, $path, $handler, $options);
    }

    public static function post(string $path, string|callable $handler, array $options = null): static
    {
        return static::addRule(__FUNCTION__, $path, $handler, $options);
    }

    public static function put(string $path, string|callable $handler, array $options = null): static
    {
        return static::addRule(__FUNCTION__, $path, $handler, $options);
    }

    public static function patch(string $path, string|callable $handler, array $options = null): static
    {
        return static::addRule(__FUNCTION__, $path, $handler, $options);
    }

    public static function head(string $path, string|callable $handler, array $options = null): static
    {
        return static::addRule(__FUNCTION__, $path, $handler, $options);
    }

    public static function delete(string $path, string|callable $handler, array $options = null): static
    {
        return static::addRule(__FUNCTION__, $path, $handler, $options);
    }

    public static function options(string $path, string|callable $handler, array $options = null): static
    {
        return static::addRule(__FUNCTION__, $path, $handler, $options);
    }

    public static function trace(string $path, string|callable $handler, array $options = null): static
    {
        return static::addRule(__FUNCTION__, $path, $handler, $options);
    }

    public static function connect(string $path, string|callable $handler, array $options = null): static
    {
        return static::addRule(__FUNCTION__, $path, $handler, $options);
    }

    public static function group(string $prefix, callable $callback, array $options = null): static
    {
        $current = self::$currentScope;
        try {
            self::$currentScope = self::create($prefix, fn() => null, $options);
            self::$currentScope->group = $current;

            call_user_func($callback, self::$currentScope);

            return self::$currentScope;
        } finally {
            self::$currentScope = $current;
        }
    }

    public static function root(callable $callback, array $options = null): void
    {
        $current = self::$currentScope;
        try {
            self::$currentScope = null;
            call_user_func($callback, $options ?? []);
        } finally {
            self::$currentScope = $current;
        }
    }

    public static function profile(string $name): ?static
    {
        return self::$profiles[$name] ?? null;
    }

    public static function fallbacks(callable $callback): void
    {
        if (!in_array($callback, self::$fallbacks, true)) {
            self::$fallbacks[] = $callback;
        }
    }

    public static function matches(ServerRequestInterface $request): ?static
    {
        $path = $request->getUri()->getPath() ?: '/';
        $method = strtoupper($request->getMethod()) ?: 'GET';


        foreach (self::$rules[$method] ?? [] as $route) {
            if ($matches = $route->match($path)) {
                return $matches;
            }
        }

        foreach (self::$fallbacks as $callable) {
            $matches = call_user_func($callable, $request);
            if ($matches instanceof static) {
                return $matches;
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return Route|null
     */
    public function getGroup(): ?self
    {
        return $this->group;
    }

    public function buildUrl(array $args = null): string
    {
        $url = preg_replace_callback('/{(\w+)}/', function (array $matches) use (&$args) {
            $key = $matches[1];
            $value = $args[$key] ?? null;
            unset($args[$key]);
            return $value;
        }, $this->group?->getPath() . $this->getPath());

        if (!empty($args)) {
            $url .= '?' . http_build_query($args);
        }

        return $url;
    }

    public function addMiddleware(string|MiddlewareInterface ...$middlewares): static
    {
        ($this->middlewareGroup ??= new MiddlewareGroup)->addMiddleware(...$middlewares);
        return $this;
    }

    public function getDefaultHandler(): callable
    {
        return fn() => 'Hello world!';
    }

    public function getHandler(): callable
    {
        return $this->handler ??= $this->getDefaultHandler();
    }

    /**
     * @return MiddlewareGroup
     */
    public function getMiddlewareGroup(): MiddlewareGroup
    {
        return $this->middlewareGroup ??= new MiddlewareGroup($this);
    }

    public function process(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getMiddlewareGroup()->handle($request);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $request = Request::fromServerRequest($request, $this->variables);

        $result = $this->execute($request, [
            Route::class => $this,
            Request::class => $request,
            ServerRequestInterface::class => $request
        ]);

        return ResponseService::context()->respond($result);
    }

    public function execute(Request $request, array $context = null): mixed
    {
        if (is_callable($this->callback)) {
            $context[Request::class] = $request;
            $context[ServerRequestInterface::class] = $request;
            $context[ParameterResolverInterface::class] = static::parameterResolver();
            return call($this->callback, $request, $context);
        }

        if (is_string($this->callback)) {
            [$class, $action] = explode('@', $this->callback . '@', 2);

            if (is_subclass_of($class, Controller::class)) {
                if (!$action) {
                    if (defined($const = $class . '::METHOD_' . strtoupper($request->getMethod() ?: 'GET'))) {
                        $action = constant($const);
                    }
                }

                if ($action && !str_starts_with($action, '_') && method_exists($class, $action)) {
                    if ($controller = app($class, true)) {
                        $this->callback = $controller->$action(...);
                        return $this->execute($request, $context);
                    }
                }
            }
        }

        throw ForbiddenException::of('The action `%s` is forbidden.', $request->getUri()->getPath());
    }

    public static function parameterResolver(): ParameterResolverInterface
    {
        static $resolver;

        if (!$resolver) {
            $resolver = clone ParameterResolver::context();
            $resolver->addResolver(new class implements ParameterResolverInterface {
                public function resolve(string $parameterName, \ReflectionNamedType $type, \ArrayAccess|array $args = null, array $context = null): mixed
                {
                    $class = $type->getName();

                    if (is_subclass_of($class, BaseRequest::class)) {
                        $arguments = $args ?? $context[Request::class];
                        if (is_null($arguments) || is_array($arguments) || ($arguments instanceof \ArrayAccess)) {
                            return new $class($arguments);
                        }
                    }

                    if (is_a($class, ServerRequestInterface::class, true)) {
                        if ($args instanceof $class) {
                            return $args;
                        }
                    }

                    if (is_subclass_of($class, EntityManagerInterface::class)) {
                        if (is_int($id = $args[$parameterName] ?? null) && ($id > 0)) {
                            return $class::find($id);
                        }
                    }

                    if (is_subclass_of($class, ModelInterface::class)) {
                        if (is_array($args)) {
                            return $class::from($args);
                        }
                    }

                    return null;
                }
            });
        }

        return $resolver;
    }

    public function setName(string $name): void
    {
        $this->name($name);
    }

    public function name(string $name): static
    {
        return self::$profiles[$name] = $this;
    }

    public function getModifiers(): string
    {
        return $this->modifiers;
    }

    /**
     * @param string $modifiers
     */
    public function setModifiers(string $modifiers): void
    {
        $this->modifiers = $modifiers;
    }

    public function getCallback(): string|callable|null
    {
        return $this->handler;
    }

    /**
     * @param string|iterable $middlewares
     */
    public function setMiddlewares(string|iterable $middlewares): void
    {
        if (is_string($middlewares)) {
            $middlewares = [$middlewares];
        }

        $this->middlewares = $middlewares;
    }

    public function compare(string $target, string $pattern): ?array
    {
        if (preg_match($pattern, $target, $matches)) {
            return array_filter($matches, 'is_string');
        }

        return null;
    }

    public function getPattern(): string
    {
        return static::parsePattern($this->group?->getPath() . $this->getPath(), $this->getModifiers());
    }

    public function match(string $target): ?static
    {
        $pattern = static::parsePattern($this->group?->getPath() . $this->getPath(), $this->getModifiers());
        if ($pattern) {
            $variables = $this->compare($target, $pattern);
            if ($variables !== null) {
                if ($variables !== $this->variables) {
                    $clone = clone $this;
                    $clone->variables = $variables;
                    return $clone;
                }

                return $this;
            }
        }
        return null;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }
}
