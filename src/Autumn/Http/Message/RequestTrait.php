<?php
/**
 * Autumn PHP Framework
 *
 * Date:        20/12/2023
 */

namespace Autumn\Http\Message;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

trait RequestTrait
{
    use MessageTrait;

    private string $requestTarget = '';
    private string $method = '';
    private ?UriInterface $uri = null;

    public static function fromRequestInterface(RequestInterface $request): static
    {
        $instance = self::fromMessageInterface($request);

        $instance->uri = $request->getUri();
        $instance->method = $request->getMethod();
        $instance->requestTarget = $request->getRequestTarget();

        return $instance;
    }

    public function getRequestTarget(): string
    {
        return $this->requestTarget;
    }

    public function withRequestTarget(string $requestTarget): static
    {
        if ($this->requestTarget === $requestTarget) {
            return $this;
        }

        $clone = clone $this;
        $this->requestTarget = $requestTarget;
        return $clone;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod(string $method): static
    {
        if ($this->method === $method) {
            return $this;
        }

        $clone = clone $this;
        $clone->method = $method;
        return $clone;
    }

    public function getUri(): UriInterface
    {
        return $this->uri ?? new Uri();
    }

    /**
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * You can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * - If the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned
     *   request.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned
     *   request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @param UriInterface $uri New request URI to use.
     * @param bool $preserveHost Preserve the original state of the Host header.
     * @return static
     */
    public function withUri(UriInterface $uri, bool $preserveHost = false): static
    {
        $clone = clone $this;
        $clone->uri = $uri;

        if (!$preserveHost) {
            $clone->updateHostHeader($uri);
        }

        return $clone;
    }

    private function updateHostHeader(UriInterface $uri): void
    {
        $host = $uri->getHost();
        $port = $uri->getPort();

        if ($host === '') {
            return;
        }

        $authority = $port ? "$host:$port" : $host;
        $this->setHeader('Host', $authority);
    }
}