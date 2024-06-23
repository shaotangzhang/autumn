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
    private array $bindings = [];      // Holds bindings for abstract classes or interfaces
    private array $contexts = [];      // Holds context data for bindings
    private array $instances = [];     // Holds singleton instances
    private array $temporaries = [];   // Holds temporary instances during construction
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
        if (isset($this->bindings[$abstract]) || isset($this->instances[$abstract])) {
            if ($context['ignore_bound'] ?? null) {
                return;
            }

            throw new \RuntimeException("The abstract `$abstract` is already bound.");
        }

        if (is_object($concrete) && !($concrete instanceof \Closure)) {
            // If concrete is an object, store it as an instance
            $this->instances[$abstract] = $concrete;
        } else {
            // Validate concrete type
            if (is_string($concrete) && !is_callable($concrete) && !class_exists($concrete)) {
                throw new \InvalidArgumentException('Binding must be a class name, callable or object.');
            }

            // Store the binding and its context
            $this->bindings[$abstract] = $concrete;
            $this->contexts[$abstract] = $context;
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
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (isset($this->temporaries[$abstract])) {
            return $this->temporaries[$abstract];
        }

        // Check if the abstract class implements SingletonInterface
        if (is_subclass_of($abstract, SingletonInterface::class)) {
            return $abstract::getInstance();
        }

        if (is_subclass_of($abstract, ContextInterface::class)) {
            return $abstract::context();
        }

        // Fetch the binding for the abstract
        $binding = $this->bindings[$abstract] ?? null;
        if (!$binding) {
            if ((env('USE_CLASS_DECORATOR_PROXY'))
                && ($binding = DecoratorProxy::createProxyClass($abstract))) {
                $this->bindings[$abstract] = $binding;
            } else {
                throw new \RuntimeException("Class '$abstract' is not bound.");
            }
        }

        // Handle callable bindings (factories)
        if (is_callable($binding)) {
            return $this->instances[$abstract] = $this->invoke($binding, [], $this->contexts[$abstract] ?? []);
        }

        // Create a new instance of the concrete class
        try {
            return $this->instances[$abstract] = $this->createInstance($binding);
        } catch (\ReflectionException $ex) {
            throw new \RuntimeException("Failed to instantiate service '$abstract'.", E_ERROR, $ex);
        }
    }

    /**
     * Check if an abstract is bound in the container.
     *
     * @param string $abstract The abstract class or interface name
     * @return bool True if the abstract is bound, false otherwise
     */
    public function isBound(string $abstract): bool
    {
        return isset($this->instances[$abstract]) || isset($this->bindings[$abstract]);
    }

    /**
     * Invoke a callable with resolved dependencies from the container context.
     *
     * @param callable $callable The callable to invoke
     * @param array|\ArrayAccess $args Optional arguments for the callable
     * @param array $context Optional context data for the callable
     * @return mixed The result of the callable invocation
     */
    public function invoke(callable $callable, array|\ArrayAccess $args = [], array $context = []): mixed
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
     * @param array $context Optional context data for the constructor
     * @return mixed The new instance of the class
     * @throws \RuntimeException If instantiation fails
     * @throws \ReflectionException If reflection fails during instantiation
     */
    protected function createInstance(string $class, array $context = []): mixed
    {
        $reflection = new \ReflectionClass($class);
        $instance = $reflection->newInstanceWithoutConstructor();

        if ($constructor = $reflection->getConstructor()) {
            $this->temporaries[$class] = $instance;
            $constructor->invoke($instance, $this->injectParameters($constructor, $context));
            unset($this->temporaries[$class]);
        }

        return $instance;
    }

    /**
     * Resolve parameters for a function or method using the container context.
     *
     * @param \ReflectionFunction|\ReflectionMethod $func The function or method reflection
     * @param array|\ArrayAccess $args Optional arguments for parameter resolution
     * @param array $context Optional context data for parameter resolution
     * @return array The resolved parameter values
     */
    protected function injectParameters(\ReflectionFunction|\ReflectionMethod $func, array|\ArrayAccess $args = [], array $context = []): array
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
            } else {
                $arguments[$name] = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;

                $type = $parameter->getType();
                if ($type instanceof \ReflectionNamedType) {
                    $types = [$type];
                } else {
                    $types = $type->getTypes();
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

    protected function resolveParameter(\ReflectionParameter       $parameter,
                                        ParameterResolverInterface $resolver = null,
                                        array|\ArrayAccess         $args = null,
                                        array                      $context = null): mixed
    {
        $name = $parameter->getName();

        if ($resolver) {
            $type = $parameter->getType();
            $types = ($type instanceof \ReflectionNamedType) ? [$type] : $type->getTypes();

            $isBuiltIn = false;
            foreach ($types as $type) {
                if (!$isBuiltIn) {
                    $isBuiltIn = $type->isBuiltin();
                }

                try {
                    // Attempt to resolve using the resolver
                    $value = $resolver->resolve($name, $type, $args, $context);
                    if ($value !== null) {
                        return $value;
                    }
                } catch (AccessDeniedException|ForbiddenException|NotFoundException|UnauthorizedException $ex) {
                    throw $ex;
                } catch (\Throwable $ex) {
                    // Log or handle the exception as needed
                    // Continue to try the next type if available
                }
            }

            if (!$isBuiltIn) {
                throw ValidationException::of('Unable to parse the value of parameter `%s`.', $name);
            }
        }

        // Fallback to $args if available
        if (isset($args[$name])) {
            return $args[$name];
        }

        // Use default value if parameter has one
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        // Throw exception if parameter is required and cannot be resolved
        if (!$parameter->allowsNull()) {
            throw ValidationException::of('The `%s` is required.', $name);
        }

        // Default to null if parameter allows null and cannot be resolved
        return null;
    }
}

