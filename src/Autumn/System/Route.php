<?php
/**
 * Autumn PHP Framework
 *
 * Date:        23/06/2024
 */

namespace Autumn\System;

use Autumn\Http\Server\RequestHandler;
use Autumn\System\Responses\ResponseService;
use Autumn\System\ServiceContainer\ParameterResolverInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Route implements RequestHandlerInterface
{
    private ?RequestHandlerInterface $handler = null;

    private mixed $callback = null;

    public function __construct(
        private string $path = '',
        private
    )
    {
    }

    public static function matches(ServerRequestInterface $request): ?static
    {
        return null;
    }

    /**
     * @return RequestHandlerInterface
     */
    public function getHandler(): RequestHandlerInterface
    {
        return $this->handler ??= new RequestHandler($this->process(...));
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->handler) {
            return $this->handler->handle($request);
        }

        return $this->process($request);
    }

    protected function process(ServerRequestInterface $request): ResponseInterface
    {
        $request = Request::fromServerRequestInterface($request, $this->variables);

        if ($this->callback instanceof \Closure) {
            $result = call($this->callback, $request, [Route::class => $this]);
            return ResponseService::context()->respond($result);
        } elseif (is_string($this->callback)) {
            [$class, $command] = explode('@', $this->callback . '@', 2);
            if (is_subclass_of($class, Controller::class)) {

                $command = trim($command)
                    ?: (defined($const = $class . '::METHOD_' . strtoupper($request->getMethod() ?: 'GET'))
                        ? constant($const)
                        : '');

                if ($command
                    && !str_starts_with($command, '_')
                    && method_exists($class, $command)) {
                    $controller = $class::context();

                    $result = call([$controller, $command], $request, [
                        Route::class => $this,
                        ParameterResolverInterface::class => $this
                    ]);

                    return ResponseService::context()->respond($result);
                }
            }
        }

        return new Response('Action `' . $request->getUri()->getPath() . '` is forbidden.', 403);
    }
}