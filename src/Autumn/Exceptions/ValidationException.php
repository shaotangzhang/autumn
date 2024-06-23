<?php

namespace Autumn\Exceptions;

use Autumn\Interfaces\Exceptional;

/**
 * Represents a validation-related exception.
 *
 * ValidationException is an exception class for errors related to validation-side operations.
 * It implements the Exceptional interface, allowing for the creation of exception instances
 * with customizable parameters through the `of()` method.
 */
class ValidationException extends \InvalidArgumentException implements Exceptional
{
    use ExceptionalTrait;

    public const ERROR_CODE = E_USER_ERROR;

    public const ERROR_MESSAGE = 'Validation exception';
}
