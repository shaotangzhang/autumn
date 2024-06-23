<?php
/**
 * Autumn PHP Framework
 *
 * Date:        20/12/2023
 */

namespace Autumn\Http\Message;

use Psr\Http\Message\ResponseInterface;

trait ResponseTrait
{
    use MessageTrait {
        setHeader as protected;
    }

    private int $statusCode = 0;
    private string $reasonPhrase = '';

    public static function fromResponseInterface(ResponseInterface $response): static
    {
        if ($response instanceof static) {
            return $response;
        }

        $instance = static::fromMessageInterface($response);
        $instance->statusCode = $response->getStatusCode();
        $instance->reasonPhrase = $response->getReasonPhrase();

        return $instance;
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function withStatus(int $code, string $reasonPhrase = ''): static
    {
        if (($code === $this->statusCode) && ($reasonPhrase === $this->reasonPhrase)) {
            return $this;
        }

        $clone = clone $this;
        $clone->setStatusCode($code);
        $clone->setReasonPhrase($reasonPhrase);
        return $clone;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * @param int $statusCode
     */
    private function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @param string $reasonPhrase
     */
    private function setReasonPhrase(string $reasonPhrase): void
    {
        $this->reasonPhrase = $reasonPhrase;
    }
}