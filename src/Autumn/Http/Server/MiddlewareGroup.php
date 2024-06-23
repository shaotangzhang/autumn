<?php

namespace Autumn\Http\Server;

use Autumn\Exceptions\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareGroup implements RequestHandlerInterface
{
    /**
     * @var array<string|MiddlewareInterface|MiddlewareGroup>
     */
    private array $middlewares = [];

    public function __construct(private ?RequestHandlerInterface $handler = null)
    {
    }

    /**
     * Gets the current request handler.
     *
     * @return RequestHandlerInterface|null
     */
    public function getHandler(): ?RequestHandlerInterface
    {
        return $this->handler;
    }

    /**
     * Sets the request handler.
     *
     * @param RequestHandlerInterface|null $handler
     */
    public function setHandler(?RequestHandlerInterface $handler): void
    {
        $this->handler = $handler;
    }

    /**
     * Adds middlewares to the group.
     *
     * @param string|MiddlewareInterface|MiddlewareGroup ...$middlewares
     * @return static
     */
    public function addMiddleware(string|self|MiddlewareInterface ...$middlewares): static
    {
        array_push($this->middlewares, ...$middlewares);
        return $this;
    }

    /**
     * Adds a named group of middlewares to the group.
     *
     * @param string $name
     * @param MiddlewareGroup|null $group
     * @return static
     */
    public function addGroup(string $name, MiddlewareGroup $group = null): static
    {
        if (!$group || !isset($this->middlewares[$name])) {
            $this->middlewares[$name] = $group ?? new static;
            return $this;
        }

        if ($this->middlewares[$name] instanceof MiddlewareGroup) {
            $this->middlewares[$name]->addMiddleware(...$group->middlewares);
        } else {
            array_unshift($group->middlewares, $this->middlewares[$name]);
            $this->middlewares[$name] = $group;
        }

        return $this;
    }

    /**
     * Handles the request by processing all middlewares.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        reset($this->middlewares);
        return $this->processNext($request);
    }

    /**
     * Processes the next middleware in the stack.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     */
    private function processNext(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = current($this->middlewares);

        if ($middleware === false) {
            if ($this->handler === null) {
                throw new NotFoundException;
            }
            return $this->handler->handle($request);
        }

        next($this->middlewares);

        if (is_string($middleware)) {
            $middleware = app($middleware, true);
        }

        if ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, $this);
        }

        if ($middleware instanceof self) {
            return $middleware->handle($request);
        }

        throw new NotFoundException;
    }
}
