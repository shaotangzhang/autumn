<?php
namespace Autumn\Exceptions;

use Autumn\Interfaces\Exceptional;

/**
 * Represents a request-related exception.
 *
 * RequestException is an exception class for errors related to request-side operations.
 * It implements the Exceptional interface, allowing for the creation of exception instances
 * with customizable parameters through the `of()` method.
 */
class RequestException extends \LogicException implements Exceptional
{
    use ExceptionalTrait;
    
    public const ERROR_CODE = 400;
    public const ERROR_MESSAGE = 'Request exception';
}
