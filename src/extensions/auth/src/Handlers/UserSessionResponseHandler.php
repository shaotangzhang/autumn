<?php

namespace Autumn\Extensions\Auth\Handlers;

use Autumn\Extensions\Auth\Models\Session\UserSession;
use Autumn\System\Responses\ResponseHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class UserSessionResponseHandler implements ResponseHandlerInterface
{
    public function respond(mixed $data, int $statusCode = null, array $context = null): ?ResponseInterface
    {
        if ($data instanceof UserSession) {

        }
    }
}