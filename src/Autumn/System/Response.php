<?php

namespace Autumn\System;

use Autumn\Http\Message\ResponseTrait;
use Psr\Http\Message\ResponseInterface;

class Response implements ResponseInterface
{
    use ResponseTrait;

    public const DEFAULT_STATUS_CODE = 200;
    public const DEFAULT_REASON_PHRASE = '';
    public const DEFAULT_VERSION = '1.0';
    public const STANDARD_VERSION = '1.1';
    private string $protocol = 'HTTP';

    private ?array $context = null;

    public function __construct(private mixed $content = null,
                                int           $statusCode = null,
                                string        $reasonPhrase = null,
                                string        $protocolVersion = null)
    {
        $this->statusCode = $statusCode ?? static::DEFAULT_STATUS_CODE;
        $this->reasonPhrase = $reasonPhrase ?? static::DEFAULT_REASON_PHRASE;
        $this->protocolVersion = $protocolVersion ?? static::DEFAULT_VERSION;
    }

    /**
     * @return array|null
     */
    public function getContext(): ?array
    {
        return $this->context;
    }

    /**
     * @param array|null $context
     */
    public function setContext(?array $context): void
    {
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function getContent(): mixed
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent(mixed $content): void
    {
        $this->content = $content;
    }

    public function send(): void
    {
        $this->sendHeaders();
        $this->sendContents();
        $this->sendFooters();
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

    protected function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        $this->sendProtocolHeaderLine();

        foreach ($this->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $this->sendHeaderLine($name, $value);
            }
        }
    }

    protected function sendContents(): void
    {
        if ($this->body) {
            stream_copy_to_stream(
                $this->body->detach(),
                fopen('php://output', 'w')
            );
        } else {
            echo $this->content;
        }
    }

    protected function sendFooters(): void
    {
    }

    public static function generateSetCookie(string                                                 $name,
                                             string                                                 $value = null,
                                             int|float|string|\DateTimeInterface|\DateInterval|null $expires = null,
                                             string                                                 $path = null,
                                             string                                                 $domain = null,
                                             bool                                                   $secure = null,
                                             bool                                                   $httpOnly = null,
                                             bool|string                                            $sameSite = null
    ): string
    {
        $cookieValue = urlencode($name) . '=' . urlencode($value ?? '');

        if ($expires !== null) {

            if ($expires instanceof \DateInterval) {
                $expires = (new \DateTime())->add($expires)->getTimestamp();
            } elseif (is_numeric($expires)) {
                $expires = intval($expires);
            } elseif (is_string($expires)) {
                $expires = strtotime($expires);
            } elseif ($expires instanceof \DateTimeInterface) {
                $expires = $expires->getTimestamp();
            }

            if ($expires && is_int($expires)) {
                $cookieValue .= '; Expires=' . gmdate('D, d-M-Y H:i:s T', $expires);
            }
        }

        if ($path !== null) {
            $cookieValue .= '; Path=' . $path;
        }

        if ($domain !== null) {
            $cookieValue .= '; Domain=' . $domain;
        }

        if ($secure) {
            $cookieValue .= '; Secure';
        }

        if ($httpOnly) {
            $cookieValue .= '; HttpOnly';
        }

        if ($sameSite) {
            $cookieValue .= '; SameSite=' . (($sameSite === true) ? 'Strict' : $sameSite);
        }

        return $cookieValue;
    }

    public function setCookie(string                                                 $name,
                              mixed                                                  $value,
                              int|float|string|\DateTimeInterface|\DateInterval|null $expires,
                              string                                                 $path = null,
                              string                                                 $domain = null,
                              bool                                                   $secure = null,
                              bool                                                   $httpOnly = null,
                              bool|string                                            $sameSite = null
    ): void
    {
        $this->addHeader('Set-Cookie', static::generateSetCookie(...func_get_args()));
    }
}
