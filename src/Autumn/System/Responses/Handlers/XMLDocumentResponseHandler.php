<?php

namespace Autumn\System\Responses\Handlers;

use Autumn\Exceptions\RedirectException;
use Autumn\Interfaces\ContextInterface;
use Autumn\Interfaces\MultipleExceptionInterface;
use Autumn\System\Response;
use Autumn\System\Responses\ResponseHandlerInterface;
use Autumn\Traits\ContextInterfaceTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * XMLDocumentResponseHandler class that handles responses with XML content.
 * This class implements the ContextInterface and ResponseHandlerInterface.
 */
class XMLDocumentResponseHandler implements ContextInterface, ResponseHandlerInterface
{
    use ContextInterfaceTrait;

    /**
     * Respond with a XML document or node.
     *
     * @param mixed $data The data to be included in the response.
     * @param int|null $statusCode The HTTP status code.
     * @param array|null $context Additional context or headers.
     * @return ResponseInterface|null The generated response or null if not handled.
     */
    public function respond(mixed $data, int $statusCode = null, array $context = null): ?ResponseInterface
    {
        if ($data instanceof \Throwable) {
            $data = $this->convertThrowableToXML($data);
        }

        if ($data instanceof \DOMDocument || $data instanceof \SimpleXMLElement) {
            $response = new Response($data, $statusCode);
            $this->setXmlContentType($response);
            return $response;
        }

        if ($data instanceof \DOMNode) {
            $response = new Response($data->ownerDocument?->saveXML($data), $statusCode);
            $this->setXmlContentType($response);
            return $response;
        }

        return null;
    }

    /**
     * Convert a Throwable object to a SimpleXMLElement for XML representation.
     *
     * @param \Throwable $exception The exception to convert.
     * @return \SimpleXMLElement The XML representation of the exception.
     */
    public function convertThrowableToXML(\Throwable $exception): \SimpleXMLElement
    {
        $xml = simplexml_load_string('<response />');
        $xml->addChild('code', $exception->getMessage());
        $xml->addChild('message', $exception->getMessage());

        if ($exception instanceof RedirectException) {
            $xml->addChild('redirect', $exception->getLocation());
        }

        if ($exception instanceof MultipleExceptionInterface) {
            foreach ($exception->getErrors() as $error) {
                $errors ??= $xml->addChild('errors');
                $child = $errors->addChild('error');
                $child->addChild('code', $error->getCode());
                $child->addChild('message', $error->getMessage());
            }
        }

        if (env('DEBUG')) {
            $xml->addChild('file', $exception->getFile());
            $xml->addChild('line', $exception->getLine());

            $trace = $xml->addChild('trace');
            foreach ($exception->getTrace() as $traceItem) {
                $traceItemElement = $trace->addChild('traceItem');
                if (isset($traceItem['file'])) {
                    $traceItemElement->addChild('file', $traceItem['file']);
                }
                if (isset($traceItem['line'])) {
                    $traceItemElement->addChild('line', $traceItem['line']);
                }
                if (isset($traceItem['function'])) {
                    $traceItemElement->addChild('function', $traceItem['function']);
                }
            }
        }

        return $xml;
    }

    /**
     * Set the Content-Type header for XML responses.
     *
     * @param ResponseInterface $response The response object to modify.
     */
    private function setXmlContentType(ResponseInterface $response): void
    {
        $response->setHeader('Content-Type', 'text/xml');
    }
}