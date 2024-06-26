<?php

namespace Autumn\System\Responses\Handlers;

use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Exceptions\RedirectException;
use Autumn\Interfaces\ArrayInterface;
use Autumn\Interfaces\ContextInterface;
use Autumn\Interfaces\MultipleExceptionInterface;
use Autumn\System\Responses\JsonResponse;
use Autumn\System\Responses\ResponseHandlerInterface;
use Autumn\Traits\ContextInterfaceTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * JsonResponseHandler class that handles responses with JSON content.
 * This class implements the ContextInterface and ResponseHandlerInterface.
 */
class JsonResponseHandler implements ContextInterface, ResponseHandlerInterface
{
    use ContextInterfaceTrait;

    /**
     * Respond with a JSON representation of the provided data.
     *
     * @param mixed $data The data to be included in the response.
     * @param int|null $statusCode The HTTP status code.
     * @param array|null $context Additional context or headers.
     * @return ResponseInterface|null The generated response or null if not handled.
     */
    public function respond(mixed $data, int $statusCode = null, array $context = null): ?ResponseInterface
    {
        if (is_object($data)) {
            if ($data instanceof \Throwable) {
                // Handle Throwable objects, such as exceptions
                $data = $this->convertThrowableToJSON($data);
                $statusCode ??= $data['code'];
            } elseif ($data instanceof ArrayInterface) {
                // Handle objects implementing ArrayInterface
                $data = ['data' => $data->toArray()];
            } elseif ($data instanceof RepositoryInterface) {
                // Handle objects implementing RepositoryInterface
                $data = ['data' => [
                    'items' => iterator_to_array($data),
                    'pagination' => $data->paginate(),
                ]];
            }
        }

        if (is_scalar($data) || is_array($data) || is_null($data) || ($data instanceof \JsonSerializable)) {
            // Handle scalar, array, null, or JsonSerializable data
            return new JsonResponse($data, $statusCode);
        }

        // Return null if the data is not handled by this handler
        return null;
    }

    public function convertThrowableToJSON(\Throwable $exception): array
    {
        $data = [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
        ];

        if ($exception instanceof RedirectException) {
            $data['redirect'] = $exception->getLocation();
        }

        if ($exception instanceof MultipleExceptionInterface) {
            foreach ($exception->getErrors() as $error) {
                $data['errors'][] = [
                    'code' => $error->getCode(),
                    'message' => $error->getMessage(),
                ];
            }
        }

        if (env('DEBUG')) {
            $data['file'] = $exception->getFile();
            $data['line'] = $exception->getLine();

            foreach ($exception->getTrace() as $index => $traceItem) {
                $trace = [];
                if (isset($traceItem['file'])) {
                    $trace['file'] = $traceItem['file'];
                }
                if (isset($traceItem['line'])) {
                    $trace['line'] = $traceItem['line'];
                }
                if (isset($traceItem['function'])) {
                    $trace['function'] = $traceItem['function'];
                }
                $data['trace'][] = $trace;
            }
        }

        return $data;
    }
}