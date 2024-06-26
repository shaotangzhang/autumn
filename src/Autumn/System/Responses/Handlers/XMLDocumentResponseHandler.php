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

class XMLDocumentResponseHandler implements ContextInterface, ResponseHandlerInterface
{
    use ContextInterfaceTrait;

    public function respond(mixed $data, int $statusCode = null, array $context = null): ?ResponseInterface
    {
        if ($data instanceof \DOMDocument || $data instanceof \SimpleXMLElement) {
            $response = new Response($data, $statusCode, $context);
            $response->setHeader('Content-Type', 'text/xml');
            return $response;
        }

        if ($data instanceof \DOMNode) {
            $response = new Response($data->ownerDocument?->saveXML($data), $statusCode, $context);
            $response->setHeader('Content-Type', 'text/xml');
            return $response;
        }

        return null;
    }
}