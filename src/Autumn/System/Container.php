<?php

namespace Autumn\System;

use Autumn\Interfaces\SingletonInterface;
use Closure;
use ReflectionClass;
use ReflectionException;

class Container
{
    /**
     * The array of bindings.
     *
     * @var array
     */
    private array $bindings = [];

    /**
     * The array of singletons.
     *
     * @var array
     */
    private array $singletons = [];

    /**
     * Bind an abstract type to a concrete implementation.
     *
     * @param string $abstract The abstract type (interface or class).
     * @param mixed $concrete The concrete implementation (class name or closure).
     * @param bool $singleton Whether the binding is a singleton.
     * @return void
     */
    public function bind(string $abstract, $concrete, bool $singleton = null): void
    {
        $this->bindings[$abstract] = $concrete;

        if ($singleton ??= is_string($concrete) && is_subclass_of($concrete, SingletonInterface::class)) {
            $this->singletons[$abstract] = null; // Mark as singleton
        }
    }

    /**
     * Bind an abstract type to a concrete implementation lazily.
     *
     * @param string $abstract
     * @param Closure|string|null $concrete
     * @param bool $singleton Whether the binding is a singleton.
     * @return void
     */
    public function bindLazy(string $abstract, $concrete = null, bool $singleton = false): void
    {
        $this->bindings[$abstract] = function () use ($abstract, $concrete) {
            return $this->build($concrete ?? $abstract);
        };

        if ($singleton) {
            $this->singletons[$abstract] = null; // Mark as singleton
        }
    }

    /**
     * Resolve an instance of the given type.
     *
     * @param string $abstract The abstract type (interface or class).
     * @return mixed The resolved instance.
     * @throws ReflectionException If the class does not exist or is not instantiable.
     */
    public function resolve(string $abstract)
    {
        // Check if the type is a singleton and already instantiated
        if (isset($this->singletons[$abstract]) && $this->singletons[$abstract] !== null) {
            return $this->singletons[$abstract];
        }

        if (!isset($this->bindings[$abstract])) {
            $instance = $this->autoBindAndResolve($abstract);
        } else {
            $concrete = $this->bindings[$abstract];

            if ($concrete instanceof Closure) {
                $instance = $concrete($this);
            } else {
                $instance = $this->build($concrete);
            }
        }

        // Store the instance if it's a singleton
        if (isset($this->singletons[$abstract])) {
            $this->singletons[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * Automatically bind and resolve an instance of the given type.
     *
     * @param string $abstract The abstract type (interface or class).
     * @return mixed The resolved instance.
     * @throws ReflectionException If the class does not exist or is not instantiable.
     */
    private function autoBindAndResolve(string $abstract)
    {
        if (class_exists($abstract)) {
            $this->bind($abstract, $abstract);
            return $this->build($abstract);
        }

        throw new ReflectionException("Class {$abstract} does not exist.");
    }

    /**
     * Build an instance of the given class.
     *
     * @param string $concrete The concrete class name.
     * @return mixed The built instance.
     * @throws ReflectionException If the class does not exist or is not instantiable.
     */
    private function build(string $concrete)
    {
        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new ReflectionException("Class {$concrete} is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete;
        }

        $parameters = $constructor->getParameters();
        $dependencies = $this->resolveDependencies($parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Resolve all dependencies for the given parameters.
     *
     * @param array $parameters The array of constructor parameters.
     * @return array The array of resolved dependencies.
     * @throws ReflectionException If a dependency cannot be resolved.
     */
    private function resolveDependencies(array $parameters): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getClass();

            if ($dependency === null) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new ReflectionException("Cannot resolve class dependency {$parameter->name}.");
                }
            } else {
                $dependencies[] = $this->resolve($dependency->name);
            }
        }

        return $dependencies;
    }
}
