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