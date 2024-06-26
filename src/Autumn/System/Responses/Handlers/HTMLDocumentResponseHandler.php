<?php
/**
 * Autumn PHP Framework
 *
 * Date:        26/06/2024
 */

namespace Autumn\System\Responses\Handlers;

use Autumn\Interfaces\ContextInterface;
use Autumn\System\Response;
use Autumn\System\Responses\ResponseHandlerInterface;
use Autumn\Traits\ContextInterfaceTrait;
use Psr\Http\Message\ResponseInterface;

class HTMLDocumentResponseHandler implements ContextInterface, ResponseHandlerInterface
{
    use ContextInterfaceTrait;

    public function respond(mixed $data, int $statusCode = null, array $context = null): ?ResponseInterface
    {
        if ($data instanceof \DOMDocument) {
            return new Response($data->saveHTML(), $statusCode, $context);
        }

        if ($data instanceof \DOMNode) {
            return new Response($data->ownerDocument?->saveHTML($data), $statusCode, $context);
        }

        return null;
    }
}