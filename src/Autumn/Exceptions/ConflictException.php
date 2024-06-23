<?php
namespace Autumn\Exceptions;

class ConflictException extends RequestException
{
    public const ERROR_CODE = 409;
    public const ERROR_MESSAGE = 'Conflict';
}