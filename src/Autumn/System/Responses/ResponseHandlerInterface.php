<?php

namespace Autumn\System\Responses;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface ResponseHandlerInterface
 * Defines the contract for response handlers that produce an HTTP response.
 */
interface ResponseHandlerInterface
{
    /**
     * Produces an HTTP response based on the provided data.
     *
     * @param mixed $data The data to be included in the response.
     * @param int|null $statusCode The HTTP status code.
     * @param array|null $context Additional context or headers.
     * @return ResponseInterface|null The generated response or null if not handled.
     */
    public function respond(mixed $data, int $statusCode = null, array $context = null): ?ResponseInterface;
}