<?php
namespace Autumn\Exceptions;

use Psr\Http\Message\ResponseInterface;

class MethodNotAllowedException extends RequestException
{
    public const ERROR_CODE = 405;
    public const ERROR_MESSAGE = 'Method Not Allowed';

    private array $allows = [];

    public function allow(string ...$methods): static
    {
        $this->allows = $methods;
        return $this;
    }

    public function prepareResponse(ResponseInterface $response): ResponseInterface
    {
        $allows = $this->allows;
        return $response->withAddedHeader('Allow', $allows);
    }
}