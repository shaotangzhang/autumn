<?php

namespace Autumn\Exceptions;

use Autumn\I18n\Locale;
use Psr\Http\Message\ResponseInterface;

trait ExceptionalTrait
{
    /**
     * Constructs a new RequestException instance.
     *
     * @param string|null $message The error message.
     * @param int|null $code The error code.
     * @param \Throwable|null $previous The previous throwable used for the exception chaining.
     */
    public function __construct(string $message = null, int $code = null, \Throwable $previous = null)
    {
        parent::__construct(
            $message ?? constant('static::ERROR_MESSAGE'),
            $code ?? constant('static::ERROR_CODE'),
            $previous
        );
    }

    public static function of(string|array|null $message, mixed ...$args): static
    {
        if (is_string($message)) {
            $message = ['message' => $message];
        }

        $message['code'] ??= constant('static::ERROR_CODE');

        $class = $message['class'] ?? static::class;
        $error = $message['message'] ?? '';

        static $classExisted;
        if ($classExisted ??= class_exists(Locale::class)) {
            $error = Locale::translate($error, $args, $message['domain'] ?? $_ENV['ERROR_DEFAULT_DOMAIN'] ?? '');
        } else {
            $error = $args ? sprintf($error, ...$args) : $error;
        }

        return new $class($error, intval($message['code'] ?? $_ENV['ERROR_DEFAULT_CODE'] ?? E_USER_ERROR));
    }

    public function prepareResponse(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    public function withCode(int $code): static
    {
        if ($this->getCode() === $code) {
            return $this;
        }

        return new static($this->getMessage(), $code, $this->getPrevious());
    }
}
