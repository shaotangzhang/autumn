<?php

namespace Autumn\System\ServiceContainer;

interface ServiceContainerInterface
{
    /**
     * Bind an abstract to a concrete implementation or a factory callback.
     *
     * @param string $abstract The abstract class or interface name
     * @param callable|object|string $concrete The concrete class name, callable, or object
     * @param array $context Optional context data for the binding
     * @throws \InvalidArgumentException If the binding type is invalid
     */
    public function bind(string $abstract, callable|object|string $concrete, array $context = []): void;

    /**
     * Check if an abstract is bound in the container.
     *
     * @param string $abstract The abstract class or interface name
     * @return bool True if the abstract is bound, false otherwise
     */
    public function isBound(string $abstract): bool;

    /**
     * Factory method to create instances of classes with support for singletons,
     * contexts, bindings, and decorators.
     *
     * @param string $abstract The abstract class name or interface.
     * @param array|null $args Optional arguments for the class constructor.
     * @param array|null $context Optional context for the instance creation.
     * @param \Throwable|null &$error Reference to capture any thrown error.
     * @return mixed The created instance or null on failure.
     */
    public function factory(string $abstract, array $args = null, array $context = null, \Throwable &$error = null): mixed;

    /**
     * Resolve an abstract to its concrete implementation or create a new instance if not bound.
     *
     * @param string $abstract The abstract class or interface name
     * @return mixed The resolved instance or value
     */
    public function make(string $abstract): mixed;

    /**
     * Invoke a callable with resolved dependencies from the container context.
     *
     * @param callable $callable The callable to invoke
     * @param array|\ArrayAccess $args Optional arguments for the callable
     * @param array $context Optional context data for the callable
     * @return mixed The result of the callable invocation
     * @throws \RuntimeException If invocation fails
     */
    public function invoke(callable $callable, array|\ArrayAccess $args = [], array $context = []): mixed;
}