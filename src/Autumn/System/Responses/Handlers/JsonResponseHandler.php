<?php
/**
 * Autumn PHP Framework
 *
 * Date:        18/06/2024
 */

namespace Autumn\System\Responses\Handlers;

use Autumn\System\Responses\JsonResponse;
use Autumn\System\Responses\ResponseHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class JsonResponseHandler implements ResponseHandlerInterface
{
    public function respond(mixed $data, int $statusCode = null, array|string $context = null): ?ResponseInterface
    {
        if (($_SERVER['HTTP_ACCEPT'] ?? null) === 'application/json') {
            if (is_scalar($data) || is_array($data) || is_null($data) || ($data instanceof \JsonSerializable)) {
                return new JsonResponse($data, $statusCode, $context);
            }
        }

        return null;
    }
}