<?php

namespace Autumn\System\ServiceContainer;

use Autumn\Exceptions\AccessDeniedException;
use Autumn\Exceptions\ForbiddenException;
use Autumn\Exceptions\NotFoundException;
use Autumn\Exceptions\UnauthorizedException;
use Autumn\Exceptions\ValidationException;
use Autumn\Interfaces\ContextInterface;
use Autumn\Interfaces\SingletonInterface;

/**
 * ServiceContainer implements a simple dependency injection container with support for bindings,
 * singleton instances, and dynamic instantiation of classes and callables.
 */
class ServiceContainer implements ServiceContainerInterface
{
    private array $abstractBindings = [];      // Holds bindings for abstract classes or interfaces
    private array $contexts = [];      // Holds context data for bindings
    private array $sharedInstances = [];     // Holds singleton instances
    private array $instancesDuringInjection = [];   // Holds temporary instances during construction
    private ?ParameterResolverInterface $parameterResolver = null;

    public static function defaultParameterResolver(): ParameterResolverInterface
    {
        return ParameterResolver::context();
    }

    /**
     * @return ParameterResolverInterface|null
     */
    public function getParameterResolver(): ?ParameterResolverInterface
    {
        return $this->parameterResolver ??= static::defaultParameterResolver();
    }

    /**
     * Bind an abstract to a concrete implementation or a factory callback.
     *
     * use $context['ignore_bound'] to set if to throw an exception on duplicate
     *
     * @param string $abstract The abstract class or interface name
     * @param callable|object|string $concrete The concrete class name, callable, or object
     * @param array $context Optional context data for the binding
     * @throws \InvalidArgumentException If the binding type is invalid
     */
    public function bind(string $abstract, callable|object|string $concrete, array $context = []): void
    {
        if (isset($this->abstractBindings[$abstract]) || isset($this->sharedInstances[$abstract])) {
            if ($context['ignore_bound'] ?? null) {
                return;
            }

            throw new \RuntimeException("The abstract `$abstract` is already bound.");
        }

        if (is_object($concrete) && !($concrete instanceof \Closure)) {
            // If concrete is an object, store it as an instance
            $this->sharedInstances[$abstract] = $concrete;
        } else {
            // Validate concrete type
            if (is_string($concrete) && !is_callable($concrete) && !class_exists($concrete)) {
                throw new \InvalidArgumentException('Binding must be a class name, callable or object.');
            }

            // Store the binding and its context
            $this->abstractBindings[$abstract] = $concrete;
            $this->contexts[$abstract] = $context;
        }
    }

    /**
     * Creates or retrieves an instance of the specified abstract class.
     *
     * @param string $abstract The abstract class or interface to resolve.
     * @param array|null $args The arguments to pass to the constructor.
     * @param array|null $context The context in which the instance is being created.
     * @param \Throwable|null $error The variable to hold any exception that occurs during instance creation.
     * @return mixed The created instance or null if an error occurs.
     */
    public function factory(string $abstract, array $args = null, array $context = null, \Throwable &$error = null): mixed
    {
        // Return existing shared instance if already created
        if (isset($this->sharedInstances[$abstract])) {
            return $this->sharedInstances[$abstract];
        }

        // Return temporary instance if it exists during injection process
        if (isset($this->instancesDuringInjection[$abstract])) {
            return $this->instancesDuringInjection[$abstract];
        }

        // Handle singleton classes
        if (is_subclass_of($abstract, SingletonInterface::class)) {
            return $abstract::getInstance();
        }

        // Handle context-aware classes
        if (is_subclass_of($abstract, ContextInterface::class)) {
            return $abstract::context();
        }

        // Resolve binding for the abstract class
        if ($binding = $this->abstractBindings[$abstract] ?? null) {
            $bindingContext = $this->contexts[$abstract] ?? $context;
            return $this->abstractBindings[$abstract] = $this->factory($binding, $args, $bindingContext);
        }

        // Handle callable bindings (factories)
        if (is_callable($binding)) {
            $bindingContext = $this->contexts[$abstract] ?? $context;
            return $this->sharedInstances[$abstract] = $this->invoke(callable: $binding, args: $args, context: $bindingContext);
        }

        // Handle decorator proxies if the environment variable is set
        static $useClassDecoratorProxy;
        if ($useClassDecoratorProxy ??= env('USE_CLASS_DECORATOR_PROXY', false)) {
            if (($proxyClass = DecoratorProxy::createProxyClass(class: $abstract))) {
                $this->abstractBindings[$abstract] = $proxyClass;
                $binding = $proxyClass;
            }
        }

        // Create a new instance of the concrete class
        try {
            return $this->sharedInstances[$abstract] = $this->createInstance(class: $binding ?? $abstract, args: $args, context: $context);
        } catch (\ReflectionException $ex) {
            // Capture and return the exception for error handling
            $error = $ex;
            return null;
        }
    }

    /**
     * Resolve an abstract to its concrete implementation or create a new instance if not bound.
     *
     * @param string $abstract The abstract class or interface name
     * @return mixed The resolved instance or value
     */
    public function make(string $abstract): mixed
    {
        $result = $this->factory($abstract, error: $error);
        if (!$result) {
            throw new \RuntimeException("Class '$abstract' is not bound.");
        }

        if ($error) {
            throw new \RuntimeException("Failed to instantiate service '$abstract'.", E_ERROR, $error);
        }

        return $result;

//
//        if (isset($this->instances[$abstract])) {
//            return $this->instances[$abstract];
//        }
//
//        if (isset($this->temporaries[$abstract])) {
//            return $this->temporaries[$abstract];
//        }
//
//        // Check if the abstract class implements SingletonInterface
//        if (is_subclass_of($abstract, SingletonInterface::class)) {
//            return $abstract::getInstance();
//        }
//
//        if (is_subclass_of($abstract, ContextInterface::class)) {
//            return $abstract::context();
//        }
//
//        // Fetch the binding for the abstract
//        $binding = $this->bindings[$abstract] ?? null;
//        if (!$binding) {
//            throw new \RuntimeException("Class '$abstract' is not bound.");
//        }
//
//        // Handle callable bindings (factories)
//        if (is_callable($binding)) {
//            return $this->instances[$abstract] = $this->invoke($binding, [], $this->contexts[$abstract] ?? []);
//        }
//
//        static $useClassDecoratorProxy;
//        if ($useClassDecoratorProxy ??= env('USE_CLASS_DECORATOR_PROXY')) {
//            if (($binding = DecoratorProxy::createProxyClass($abstract))) {
//                $this->bindings[$abstract] = $binding;
//            }
//        }
//
//        // Create a new instance of the concrete class
//        try {
//            return $this->instances[$abstract] = $this->createInstance($binding);
//        } catch (\ReflectionException $ex) {
//            throw new \RuntimeException("Failed to instantiate service '$abstract'.", E_ERROR, $ex);
//        }
    }

    /**
     * Check if an abstract is bound in the container.
     *
     * @param string $abstract The abstract class or interface name
     * @return bool True if the abstract is bound, false otherwise
     */
    public function isBound(string $abstract): bool
    {
        return isset($this->sharedInstances[$abstract]) || isset($this->abstractBindings[$abstract]);
    }

    /**
     * Invoke a callable with resolved dependencies from the container context.
     *
     * @param callable $callable The callable to invoke
     * @param array|\ArrayAccess|null $args Optional arguments for the callable
     * @param array|null $context Optional context data for the callable
     * @return mixed The result of the callable invocation
     */
    public function invoke(callable $callable, array|\ArrayAccess $args = null, array $context = null): mixed
    {
        try {
            if (is_array($callable)) {
                [$class, $method] = $callable;
                $func = new \ReflectionMethod($class, $method);
                return $func->invokeArgs($class, $this->injectParameters($func, $args, $context));
            }

            $func = new \ReflectionFunction($callable);
            return $func->invokeArgs($this->injectParameters($func, $args, $context));
        } catch (\ReflectionException $e) {
            throw new \RuntimeException($e->getMessage(), E_ERROR, $e);
        }
    }

    /**
     * Create a new instance of a class with resolved constructor dependencies.
     *
     * @param string $class The class name to instantiate
     * @param array|null $args Optional arguments for the constructor
     * @param array|null $context Optional context data for the constructor
     * @return mixed The new instance of the class
     * @throws \ReflectionException If reflection fails during instantiation
     */
    protected function createInstance(string $class, array $args = null, array $context = null): mixed
    {
        $reflection = new \ReflectionClass($class);
        if (!$reflection->isInstantiable()) {
            throw new \ReflectionException('The abstract `' . $class . '` is not instantiable.');
        }

        $instance = $reflection->newInstanceWithoutConstructor();

        if ($constructor = $reflection->getConstructor()) {
            $this->instancesDuringInjection[$class] = $instance;
            $constructor->invoke($instance, $this->injectParameters($constructor, $args, $context));
            unset($this->instancesDuringInjection[$class]);
        }

        return $instance;
    }

    /**
     * Resolve parameters for a function or method using the container context.
     *
     * @param \ReflectionFunction|\ReflectionMethod $func The function or method reflection
     * @param array|\ArrayAccess|null $args Optional arguments for parameter resolution
     * @param array|null $context Optional context data for parameter resolution
     * @return array The resolved parameter values
     */
    protected function injectParameters(\ReflectionFunction|\ReflectionMethod $func, array|\ArrayAccess $args = null, array $context = null): array
    {
        $resolver = null;
        $arguments = [];
        foreach ($func->getParameters() as $offset => $parameter) {

            if ($resolver === null) {
                $resolver = $context[ParameterResolverInterface::class] ?? null;
                if (!$resolver instanceof ParameterResolverInterface) {
                    $resolver = $this->getParameterResolver();
                }
            }

            $name = $parameter->getName();
            if (isset($args[$name])) {
                $arguments[$name] = $args[$name];
            } elseif (isset($args[$offset])) {
                $arguments[$name] = $args[$offset];
            } else if ($name === 'context') {
                $arguments[$name] = $context;
            } else {
                $arguments[$name] = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;

                $type = $parameter->getType();
                if ($type instanceof \ReflectionNamedType) {
                    $types = [$type];
                } else {
                    $types = $type?->getTypes() ?? [];
                }

                foreach ($types as $type) {
                    if (!$type->isBuiltin()) {
                        $class = $type->getName();
                        if (isset($context[$class])) {
                            $arguments[$name] = $context[$class];
                            break;
                        }

                        if ($value = $resolver->resolve($name, $type, $args, $context)) {
                            $arguments[$name] = $value;
                            break;
                        }
                    }
                }
            }
        }

        return $arguments;
    }
}
