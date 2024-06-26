<?php
/**
 * Autumn PHP Framework
 *
 * Date:        18/06/2024
 */

namespace Autumn\System\Responses\Handlers;

use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Exceptions\RedirectException;
use Autumn\Interfaces\ArrayInterface;
use Autumn\Interfaces\ContextInterface;
use Autumn\System\Responses\JsonResponse;
use Autumn\System\Responses\ResponseHandlerInterface;
use Autumn\Traits\ContextInterfaceTrait;
use Psr\Http\Message\ResponseInterface;

class JsonResponseHandler implements ContextInterface, ResponseHandlerInterface
{
    use ContextInterfaceTrait;

    public function respond(mixed $data, int $statusCode = null, array $context = null): ?ResponseInterface
    {
        $reasonPhrase = $context['reasonPhrase'] ?? $context['message'] ?? null;

        if (is_object($data)) {
            if ($data instanceof \Throwable) {
                $data = [
                    'code' => $data->getCode(),
                    'message' => $data->getMessage(),
                    'redirect' => ($data instanceof RedirectException) ? $data->getLocation() : null,
                ];
                $reasonPhrase ??= $data['message'];
                $statusCode ??= $data['code'];
            } elseif ($data instanceof ArrayInterface) {
                // model, view, entities, ...
                $data = ['data' => $data->toArray()];
            } elseif ($data instanceof RepositoryInterface) {
                // query
                $data = ['data' => [
                    'items' => iterator_to_array($data),
                    'pagination' => $data->paginate(),
                ]];
            }
        }

        if (is_scalar($data) || is_array($data) || is_null($data) || ($data instanceof \JsonSerializable)) {
            return new JsonResponse($data, $statusCode, $reasonPhrase);
        }

        return null;
    }
}