<?php
/**
 * Autumn PHP Framework
 *
 * Date:        25/02/2024
 */

namespace Autumn\Http;

use Autumn\Http\Message\RequestTrait;
use Autumn\Http\Message\Uri;
use Psr\Http\Message\RequestInterface;

class HttpRequest implements RequestInterface
{
    use RequestTrait;

    public function __construct(string $method, string $url, array $headers = null)
    {
        $this->method = $method;
        $this->uri = new Uri($url);

        if ($headers) {
            $this->setHeaders($headers);
        }
    }
}