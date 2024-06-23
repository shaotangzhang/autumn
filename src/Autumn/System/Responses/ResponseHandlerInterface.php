<?php
/**
 * Autumn PHP Framework
 *
 * Date:        21/05/2024
 */

namespace Autumn\System\Responses;

use Psr\Http\Message\ResponseInterface;

interface ResponseHandlerInterface
{
    public function respond(mixed $data, int $statusCode = null, string|array $context = null): ?ResponseInterface;
}