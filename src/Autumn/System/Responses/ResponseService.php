<?php
/**
 * Autumn PHP Framework
 *
 * Date:        23/06/2024
 */

namespace Autumn\System\Responses;

use Autumn\Interfaces\ContextInterface;
use Autumn\Traits\ContextInterfaceTrait;
use Psr\Http\Message\ResponseInterface;

class ResponseService implements ContextInterface, ResponseHandlerInterface
{
    use ContextInterfaceTrait;

    public function respond(mixed $data, int $statusCode = null, array $context = null): ?ResponseInterface
    {

    }
}