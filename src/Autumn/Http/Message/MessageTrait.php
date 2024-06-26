<?php
namespace Autumn\Http\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

trait MessageTrait
{
    use HeadersTrait;
    private string $protocolVersion = '';
    private ?StreamInterface $body = null;

    public static function fromMessageInterface(MessageInterface $message): static
    {
        if ($message instanceof static) {
            return $message;
        }

        $instance = new static;

        $instance->headers = $message->getHeaders();
        $instance->protocolVersion = $message->getProtocolVersion();
        $instance->body = $message->getBody();

        return $instance;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @param string $protocolVersion
     */
    private function setProtocolVersion(string $protocolVersion): void
    {
        $this->protocolVersion = $protocolVersion;
    }

    public function withProtocolVersion(string $version): static
    {
        if ($this->protocolVersion === $version) {
            return $this;
        }

        $clone = clone $this;
        $this->protocolVersion = $version;
        return $clone;
    }

    /**
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->body ??= Stream::temp();
    }

    /**
     * @param StreamInterface|null $body
     */
    private function setBody(?StreamInterface $body): void
    {
        $this->body = $body;
    }

    public function withBody(StreamInterface $body): static
    {
        if ($body === $this->body) {
            return $this;
        }

        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }
}