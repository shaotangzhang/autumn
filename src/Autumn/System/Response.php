<?php
/**
 * Autumn PHP Framework
 *
 * Date:        23/06/2024
 */

namespace Autumn\System;

use Autumn\Http\Message\ResponseTrait;
use Psr\Http\Message\ResponseInterface;

class Response implements ResponseInterface
{
    use ResponseTrait;

    public const DEFAULT_STATUS_CODE = 200;
    public const DEFAULT_REASON_PHRASE = '';
    public const DEFAULT_PROTOCOL = 'HTTP';
    public const DEFAULT_PROTOCOL_VERSION = '1.0';

    private string $protocol;

    public function __construct(private readonly ?string $content = null, int $statusCode = null, string $reasonPhrase = null, string $protocolVersion = null)
    {
        $this->setStatusCode($statusCode ?? static::DEFAULT_STATUS_CODE);
        $this->setReasonPhrase($reasonPhrase ?? static::DEFAULT_REASON_PHRASE);

        $this->setProtocol(static::DEFAULT_PROTOCOL);
        $this->setProtocolVersion($protocolVersion ?? static::DEFAULT_PROTOCOL_VERSION);
    }

    /**
     * @return string
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }

    /**
     * @param string $protocol
     */
    public function setProtocol(string $protocol): void
    {
        $this->protocol = $protocol;
    }

    public function send(): void
    {
        $this->sendHeaders();
        $this->sendContent();
        $this->sendFooters();
    }

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

    protected function sendHeaderLine(string $name, string $value): void
    {
        header(sprintf('%s: %s', $name, $value), false);
    }

    protected function sendFooters(): void
    {
        // do nothing here at the moment
    }

    protected function sendContent(): void
    {
        if ($stream = $this->body?->detach()) {
            stream_copy_to_stream($stream, fopen('php://output', 'w'));
        } else {
            echo $this->content;
        }
    }
}