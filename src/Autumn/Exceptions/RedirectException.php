<?php
namespace Autumn\Exceptions;

use Psr\Http\Message\ResponseInterface;

class RedirectException extends RequestException
{
    public const ERROR_CODE = 302;
    public const ERROR_MESSAGE = 'Moved temporary';

    private ?string $location = '';
    private int $delay = 0;

    public static function to(string $location, int $statusCode = null, string $reasonPhrase = null, \Throwable $previous = null): static
    {
        $instance = new static($reasonPhrase, $statusCode, $previous);
        $instance->location = $location;
        return $instance;
    }

    public function withLocation(string $location, array $args = null): static
    {
        if ($args) {
            [$path, $query] = explode('?', $location . '?', 2);
            if ($query) {
                parse_str(trim($query, '?'), $params);
                $args = array_merge($params, $args);
            }

            $location = $path . '?' . http_build_query($args);
        }

        if ($location === $this->location) {
            return $this;
        }

        $clone = new static($this->message, $this->code, $this->getPrevious());
        // $clone = clone $this;
        $this->location = $location;
        return $clone;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @param string|null $location
     */
    public function setLocation(?string $location): void
    {
        $this->location = $location;
    }

    /**
     * @return float
     */
    public function getDelay(): float
    {
        return $this->delay / 1000.0;
    }

    /**
     * @param int $delay
     */
    public function setDelay(int $delay): void
    {
        $this->delay = $delay;
    }

    public function withDelay(int|float $seconds): static
    {
        $delay = (int)($seconds * 1000);
        if ($this->delay === $delay) {
            return $this;
        }

        $clone = clone $this;
        $clone->delay = $delay;
        return $clone;
    }

    public function prepareResponse(ResponseInterface $response): ResponseInterface
    {
        if ($this->delay > 0) {
            $response = $response->withHeader('Redirect-After', $this->delay / 1000);
        }

        if ($location = $this->getLocation()) {
            return $response->withHeader('location', $location);
        }

        return $response;
    }
}