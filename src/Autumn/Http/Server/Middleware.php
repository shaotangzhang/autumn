<?php

namespace Autumn\Http\Server;

use ArrayAccess;
use Autumn\Attributes\Transient;
use Autumn\System\Application;
use Autumn\System\ClassFactory;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Middleware implements MiddlewareInterface
{
    #[Transient]
    private mixed $middleware;

    public function __construct(callable $middleware)
    {
        $this->middleware = $middleware;
    }

    public static function pipeline(iterable $list, ServerRequestInterface $request, callable|RequestHandlerInterface $handler, array|ArrayAccess $context = null): ResponseInterface
    {
        if (!$handler instanceof RequestHandlerInterface) {
            $handler = new RequestHandler($handler);
        }

        foreach ($list as $middleware) {
            if (is_subclass_of($middleware, MiddlewareInterface::class)) {
                if (is_string($middleware)) {
                    $middleware = ClassFactory::make($middleware, null, $context);
                }

                $handler = new RequestHandler(function (ServerRequestInterface $request) use ($handler, $middleware) {
                    return $middleware->process($request, $handler);
                });
            } elseif (is_string($middleware)) {
                $handler = new RequestHandler(function (ServerRequestInterface $request) use ($handler, $middleware, $context) {
                    return Application::context()->handleMiddlewareGroup($middleware, $request, $handler, $context);
                });
            }
        }

        return $handler->handle($request);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return call_user_func($this->middleware, $request, $handler);
    }
}
