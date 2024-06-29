<?php

namespace Autumn\Extensions\Auth\Middlewares;

use Autumn\Extensions\Auth\Models\Role\Role;
use Autumn\Extensions\Auth\Services\AuthService;
use Autumn\System\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoginUserMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requiredRole = Role::USER;

        $roles = AuthService::context()->hasRoles($requiredRole);
        if ($roles === null) {
            return Response::forbidden('Access denied.');
        }

        if (!$roles) {
            return Response::redirect('/login', [
                'error' => 'Login is required.',
                'redirect' => $request->getUri()->getPath()
            ]);
        }

        return $handler->handle($request);
    }
}
