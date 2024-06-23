<?php
/**
 * Autumn PHP Framework
 *
 * Date:        18/06/2024
 */

namespace Autumn\System\Responses\Handlers;

use Autumn\Exceptions\RedirectException;
use Autumn\System\Response;
use Autumn\System\Responses\JsonResponse;
use Autumn\System\Responses\RedirectResponse;
use Autumn\System\Responses\RenderableResponse;
use Autumn\System\Responses\ResponseHandlerInterface;
use PHPUnit\Util\Json;
use Psr\Http\Message\ResponseInterface;

class ThrowableResponseHandler implements ResponseHandlerInterface
{

    public function respond(mixed $data, int $statusCode = null, array|string $context = null): ?ResponseInterface
    {
        if ($data instanceof \Throwable) {

            if ($data instanceof RedirectException) {
                return new RedirectResponse($data->getLocation(), $data->getCode(), $data->getMessage());
            }

            if (($_SERVER['HTTP_ACCEPT'] ?? null) === 'application/json') {
                $data = [
                    'code' => $statusCode ?: $data->getCode() ?: 500,
                    'message' => $context['reasonPhrase'] ?? $context['message'] ?? null
                ];

                return new JsonResponse($data, $data['code'], $data['message']);
            }

            return new Response(null, $data->getCode(), $data->getMessage());
        }

        return null;
    }
}