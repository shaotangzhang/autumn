<?php
/**
 * Autumn PHP Framework
 *
 * Date:        23/06/2024
 */

namespace Autumn\System;

use Autumn\Http\Message\MessageTrait;
use Autumn\Http\Message\ResponseTrait;
use Autumn\Interfaces\ArrayInterface;
use Autumn\Interfaces\Renderable;
use Psr\Http\Message\ResponseInterface;

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

    public function __construct(private readonly mixed $content = null, int $statusCode = null, string|array $reasonPhrase = null, string $protocolVersion = null)
    {
        if (is_array($reasonPhrase)) {
            $reasonPhrase = $reasonPhrase['reasonPhrase'] ?? $reasonPhrase['message'] ?? null;
        }

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

    /**
     * @return mixed
     */
    public function getContent(): mixed
    {
        return $this->content;
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