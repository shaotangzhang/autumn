<?php

namespace Autumn\Extensions\Auth\Middlewares;

use Autumn\Extensions\Auth\Models\Role\Role;
use Autumn\Extensions\Auth\Services\AuthService;
use Autumn\System\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoginAdminMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requiredRole = Role::ADMIN;

        $session = AuthService::session();
        if (!$session) {
            return Response::redirect('/login', [
                'error' => 'Login is required.',
                'redirect' => $request->getUri()->getPath()
            ]);
        }

        if (!in_array($requiredRole, $session->getAuthorities(), true)) {
            if (!in_array(Role::SUPERVISOR, $session->getAuthorities(), true)) {
                return Response::forbidden('Access denied.');
            }
        }

        return $handler->handle($request);
    }
}
