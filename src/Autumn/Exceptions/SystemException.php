<?php
namespace Autumn\Exceptions;

use Autumn\Interfaces\Exceptional;

/**
 * Represents a system-related exception.
 *
 * SystemException is an exception class for errors related to system-side operations.
 * It implements the Exceptional interface, allowing for the creation of exception instances
 * with customizable parameters through the `of()` method.
 */
class SystemException extends \RuntimeException implements Exceptional
{
    use ExceptionalTrait;
    
    public const ERROR_CODE = E_ERROR;
    public const ERROR_MESSAGE = 'System Exception';
}
