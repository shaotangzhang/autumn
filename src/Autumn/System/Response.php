<?php

namespace Autumn\System;

use Autumn\Http\Message\ResponseTrait;
use Autumn\Interfaces\ArrayInterface;
use Autumn\Interfaces\Renderable;
use Psr\Http\Message\ResponseInterface;

/**
 * Response class implementing PSR-7 ResponseInterface.
 * This class represents an HTTP response and provides methods for
 * setting headers, status codes, content, and sending the response.
 */
class Response implements ResponseInterface
{
    use ResponseTrait {
        setHeader as public;
    }

    public const DEFAULT_STATUS_CODE = 200;
    public const DEFAULT_REASON_PHRASE = '';
    public const DEFAULT_PROTOCOL = 'HTTP';
    public const DEFAULT_PROTOCOL_VERSION = '1.0';

    private string $protocol;

    /**
     * Constructor to initialize the Response object.
     *
     * @param mixed $content The content of the response.
     * @param int|null $statusCode The HTTP status code.
     * @param string|null $reasonPhrase The reason phrase.
     * @param string|null $protocolVersion The HTTP protocol version.
     */
    public function __construct(
        private readonly mixed $content = null,
        int                    $statusCode = null,
        string                 $reasonPhrase = null,
        string                 $protocolVersion = null
    )
    {
        $this->setStatusCode($statusCode ?? static::DEFAULT_STATUS_CODE);
        $this->setReasonPhrase($reasonPhrase ?? static::DEFAULT_REASON_PHRASE);
        $this->setProtocol(static::DEFAULT_PROTOCOL);
        $this->setProtocolVersion($protocolVersion ?? static::DEFAULT_PROTOCOL_VERSION);
    }

    /**
     * Get the HTTP protocol.
     *
     * @return string The protocol.
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }

    /**
     * Set the HTTP protocol.
     *
     * @param string $protocol The protocol to set.
     */
    public function setProtocol(string $protocol): void
    {
        $this->protocol = $protocol;
    }

    /**
     * Get the content of the response.
     *
     * @return mixed The response content.
     */
    public function getContent(): mixed
    {
        return $this->content;
    }

    /**
     * Send the response headers, content, and footers.
     */
    public function send(): void
    {
        $this->sendHeaders();
        $this->sendContent();
        $this->sendFooters();
    }

    /**
     * Send the response headers.
     *
     * @throws \RuntimeException If headers are already sent.
     */
    protected function sendHeaders(): void
    {
        if (headers_sent()) {
            if (count($this->headers) > 0) {
                throw new \RuntimeException('HTTP headers are already sent.');
            }
        }

        $this->sendProtocolHeaderLine();

        foreach ($this->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $this->sendHeaderLine($name, $value);
            }
        }
    }

    /**
     * Send the HTTP protocol header line.
     */
    protected function sendProtocolHeaderLine(): void
    {
        $line = sprintf(
            '%s/%s %d %s',
            $this->getProtocol(),
            $this->getProtocolVersion(),
            $this->getStatusCode(),
            $this->getReasonPhrase()
        );

        header($line, true, $this->getStatusCode());
    }

    /**
     * Send a single header line.
     *
     * @param string $name The header name.
     * @param string $value The header value.
     */
    protected function sendHeaderLine(string $name, string $value): void
    {
        header(sprintf('%s: %s', $name, $value), false);
    }

    /**
     * Send response footers.
     * Currently, does nothing but can be extended in the future.
     */
    protected function sendFooters(): void
    {
        // do nothing here at the moment
    }

    /**
     * Send the response content.
     */
    protected function sendContent(): void
    {
        if ($stream = $this->body?->detach()) {
            stream_copy_to_stream($stream, fopen('php://output', 'w'));
            return;
        }

        $content = $this->content;
        while ($content instanceof \Closure) {
            $content = $content();
        }

        if ($content instanceof Renderable) {
            $content->render();
            return;
        }

        if ($content instanceof \DateTimeInterface) {
            echo $content->format('c');
            return;
        }

        if ($content instanceof ArrayInterface) {
            $content = $content->toArray();
        }

        if (is_array($content) || $content instanceof \JsonSerializable) {
            echo json_encode($content);
            return;
        }

        if ($content instanceof \SimpleXMLElement || $content instanceof \DOMDocument) {
            echo $content->saveXML();
            return;
        }

        echo $content;
    }
}
