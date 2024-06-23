<?php
namespace Autumn\Exceptions;

use Psr\Http\Message\ResponseInterface;

class ServiceUnavailableException extends ServerException
{
    public const ERROR_CODE = 500;
    public const ERROR_MESSAGE = 'Service Unavailable';

    private int|string|null $retryAfter = null;

    public function withRetryAfter(int|string $retryAfter): static
    {
        if ($this->retryAfter === $retryAfter) {
            return $this;
        }

        $clone = clone $this;
        $this->retryAfter = $retryAfter;
        return $clone;
    }

    /**
     * @return int|string|null
     */
    public function getRetryAfter(): int|string|null
    {
        return $this->retryAfter;
    }

    public function prepareResponse(ResponseInterface $response): ResponseInterface
    {
        if ($retryAfter = $this->getRetryAfter()) {
            return $response->withHeader('Retry-After', $retryAfter);
        }

        return $response;
    }
}