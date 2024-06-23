<?php
/**
 * Autumn PHP Framework
 *
 * Date:        1/04/2024
 */

namespace Autumn\Interfaces;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface Exceptional
 *
 * Exceptional:
 *  SystemException extends RuntimeException
 *  ServerException extends Exception
 *      ...Http5xxExceptions
 *  RequestException extends LogicException
 *      RedirectException extends RequestException
 *          ...Http3xxExceptions
 *      UnauthorizedException extends RequestException
 *          AuthenticationException extends UnauthorizedException
 *      ...Http4xxExceptions
 *  ValidationException extends InvalidArgumentException
 *
 * Exceptional represents an interface for exceptions that can be created using the `of()` method,
 * providing flexibility in constructing exception instances with various parameters.
 */
interface Exceptional extends \Throwable
{
    /**
     * Create an instance of the implementing exception class.
     *
     * This method allows creating exception instances with customizable parameters,
     * such as message, code, and domain.
     *
     * @param string|array $message The error message or an array containing message, code, and domain.
     * @param mixed ...$args Additional arguments for formatting the error message if it contains placeholders.
     * @return static An instance of the implementing exception class.
     */
    public static function of(string|array $message, mixed ...$args): static;

    /**
     * Prepares a response for the exception.
     *
     * This method allows the exception class to customize the response to be returned when the exception occurs. It accepts
     * the current response object and returns a modified or new response object that represents the appropriate error response
     * for the exception.
     *
     * @param ResponseInterface $response The current response object.
     * @return ResponseInterface The prepared response object.
     */
    public function prepareResponse(ResponseInterface $response): ResponseInterface;
}
