<?php
namespace Autumn\Exceptions;

class PreconditionRequiredException extends RequestException
{
    public const ERROR_CODE = 400;
    public const ERROR_MESSAGE = 'Precondition Required';
}