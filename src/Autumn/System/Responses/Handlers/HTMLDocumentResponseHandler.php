<?php

namespace Autumn\System\Responses\Handlers;

use Autumn\Interfaces\ContextInterface;
use Autumn\System\Response;
use Autumn\System\Responses\ResponseHandlerInterface;
use Autumn\Traits\ContextInterfaceTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * HTMLDocumentResponseHandler class that handles responses with HTML content.
 * This class implements the ContextInterface and ResponseHandlerInterface.
 */
class HTMLDocumentResponseHandler implements ContextInterface, ResponseHandlerInterface
{
    use ContextInterfaceTrait;

    /**
     * Respond with a HTML document or node.
     *
     * @param mixed $data The data to be included in the response.
     * @param int|null $statusCode The HTTP status code.
     * @param array|null $context Additional context or headers.
     * @return ResponseInterface|null The generated response or null if not handled.
     */
    public function respond(mixed $data, int $statusCode = null, array $context = null): ?ResponseInterface
    {
        if ($data instanceof \DOMDocument) {
            // If the data is a DOMDocument, return a Response with the HTML content
            return new Response($data->saveHTML(), $statusCode);
        }

        if ($data instanceof \DOMNode) {
            // If the data is a DOMNode, return a Response with the HTML content of the node
            return new Response($data->ownerDocument?->saveHTML($data), $statusCode);
        }

        // Return null if the data is not handled by this handler
        return null;
    }
}