<?php
namespace Autumn\Exceptions;

use Autumn\Interfaces\Exceptional;

/**
 * Represents a server-related exception.
 *
 * ServerException is an exception class for errors related to server-side operations.
 * It implements the Exceptional interface, allowing for the creation of exception instances
 * with customizable parameters through the `of()` method.
 */
class ServerException extends \Exception implements Exceptional
{
    use ExceptionalTrait;

    public const ERROR_CODE = 500;
    public const ERROR_MESSAGE = 'Server Exception';

    /**
     * Constructs a new ServerException instance.
     *
     * @param string|null $message The error message.
     * @param int|null $code The error code.
     * @param \Throwable|null $previous The previous throwable used for the exception chaining.
     */
    public function __construct(string $message = null, int $code = null, \Throwable $previous = null)
    {
        parent::__construct(
            $message ?? self::ERROR_MESSAGE,
            $code ?? static::ERROR_CODE ?: self::ERROR_CODE,
            $previous
        );
    }
}
