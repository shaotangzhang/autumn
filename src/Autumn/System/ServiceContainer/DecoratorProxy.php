<?php

namespace Autumn\System\ServiceContainer;

use Autumn\App;
use Autumn\Attributes\Decorated;
use Autumn\Interfaces\DecoratorInterface;
use Autumn\System\ClassFactory\ClassFile;
use Autumn\System\ClassFactory\Method;

class DecoratorProxy
{
    /**
     * @var array<string, false|string>
     */
    private static array $proxies = [];

    /**
     * Decorates a given method using its attributes.
     *
     * @param string $method The method name to be decorated.
     * @param callable $callback The original method callback.
     * @param array $args The arguments to be passed to the method.
     * @param array|null $context The context in which the method is called.
     * @return mixed The result of the decorated method call.
     */
    public static function decorate(string $method, callable $callback, array $args = [], array $context = null): mixed
    {
        static $cachedCallbacks = [];

        if (!isset($cachedCallbacks[$method])) {
            $cachedCallbacks[$method] = function ($func) use ($method, $args, $context): callable {

                $callback = fn($args) => call_user_func_array($func, $args);

                try {
                    $reflection = new \ReflectionMethod($method);
                } catch (\ReflectionException) {
                    return call($callback, $args, $context);
                }

                $context = [\ReflectionMethod::class => $reflection];

                foreach ($reflection->getAttributes() as $attribute) {
                    if (is_subclass_of($attribute->getName(), DecoratorInterface::class)) {
                        $callback = function ($args) use ($attribute, $callback, $context) {
                            return $attribute->newInstance()->decorate($callback, $args, $context);
                        };
                    }
                }

                return $callback;
            };
        }

        return call_user_func_array($cachedCallbacks[$method]($callback), $args);
    }

    /**
     * Creates a decorating proxy class for the given abstract class.
     *
     * @param string $abstract The fully qualified name of the abstract class.
     * @return string|null The fully qualified name of the proxy class, or null if it could not be created.
     */
    public static function createProxyClass(string $abstract): ?string
    {
        $abstract = trim($abstract, '/\\');
        if (isset(self::$proxies[$abstract])) {
            return self::$proxies[$abstract];
        }

        self::$proxies[$abstract] = false;

        try {
            $reflection = new \ReflectionClass($abstract);
            if (empty($reflection->getAttributes(Decorated::class))) {
                return null;
            }
        } catch (\ReflectionException) {
            return null;
        }

        $proxyClass = 'Temp\\' . $abstract;
        $file = App::map('storage', 'classes', strtr($proxyClass, '\\', DIRECTORY_SEPARATOR) . '.php');

        if (self::isProxyUpToDate($file, $reflection->getFileName())) {
            return self::$proxies[$abstract] = $proxyClass;
        }

        if (!self::prepareDirectory($file)) {
            return null;
        }

        $classFile = self::generateClassFile($reflection, $proxyClass, $abstract);

        if ($classFile && file_put_contents($file, (string)$classFile)) {
            return self::$proxies[$abstract] = $proxyClass;
        }

        return null;
    }

    /**
     * Checks if the proxy file is up-to-date compared to the abstract class file.
     *
     * @param string $file The proxy file path.
     * @param string $compareFile The abstract class file path.
     * @return bool True if the proxy file is up-to-date, false otherwise.
     */
    private static function isProxyUpToDate(string $file, string $compareFile): bool
    {
        if (!is_file($file) || !is_file($compareFile)) {
            return false;
        }

        $fileTime = filemtime($file);
        $abstractFileTime = filemtime($compareFile);
        return $fileTime >= $abstractFileTime;
    }

    /**
     * Prepares the directory for the proxy file.
     *
     * @param string $file The proxy file path.
     * @return bool True if the directory exists or was created successfully, false otherwise.
     */
    private static function prepareDirectory(string $file): bool
    {
        $path = dirname($file);
        return is_dir($path) || mkdir($path, 0777, true);
    }

    /**
     * Generates the proxy class file based on the abstract class.
     *
     * @param \ReflectionClass $reflection The reflection class of the abstract class.
     * @param string $proxyClass The fully qualified name of the proxy class.
     * @param string $abstract The fully qualified name of the abstract class.
     * @return ClassFile|null The generated class file, or null if no modifications were made.
     */
    private static function generateClassFile(\ReflectionClass $reflection, string $proxyClass, string $abstract): ?ClassFile
    {
        $modified = false;
        $container = '\\' . static::class;
        $classFile = new ClassFile($proxyClass, $abstract);

        foreach ($reflection->getMethods() as $method) {
            if (self::hasDecoratorAttributes($method)) {
                $modifiedMethod = Method::fromReflectionMethod($method);
                $methodBody = self::generateMethodBody($method, $container);
                $modifiedMethod->setCodes($methodBody);
                $classFile->addMethod($modifiedMethod);
                $modified = true;
            }
        }

        return $modified ? $classFile : null;
    }

    /**
     * Checks if the method has any decorator attributes.
     *
     * @param \ReflectionMethod $method The reflection method.
     * @return bool True if the method has decorator attributes, false otherwise.
     */
    private static function hasDecoratorAttributes(\ReflectionMethod $method): bool
    {
        foreach ($method->getAttributes() as $attribute) {
            if (is_subclass_of($attribute->getName(), DecoratorInterface::class)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Generates the method body for the proxy class method.
     *
     * @param \ReflectionMethod $method The reflection method.
     * @param string $container The container class name.
     * @return array The generated method body lines.
     */
    private static function generateMethodBody(\ReflectionMethod $method, string $container): array
    {
        $callback = '$callback = fn($args) => call_user_func_array(\'parent::\' . __FUNCTION__, $args);';
        $decorateCall = $container . '::decorate(__METHOD__, $callback, func_get_args());';

        if ($method->getReturnType() && $method->getReturnType()->getName() === 'void') {
            return [$callback, $decorateCall];
        }

        return [$callback, 'return ' . $decorateCall];
    }
}