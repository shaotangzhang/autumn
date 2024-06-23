<?php
/**
 * Autumn PHP Framework
 *
 * Date:        18/06/2024
 */

namespace Autumn\System\Responses\Handlers;

use Autumn\Interfaces\Renderable;
use Autumn\System\Responses\RenderableResponse;
use Autumn\System\Responses\ResponseHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class RenderableResponseHandler implements ResponseHandlerInterface
{
    public function respond(mixed $data, int $statusCode = null, array $context = null): ?ResponseInterface
    {
        if ($data instanceof Renderable) {
            return new RenderableResponse($data, $statusCode, $context['reasonPhrase'] ?? $context['message'] ?? null);
        }

        return null;
    }
}