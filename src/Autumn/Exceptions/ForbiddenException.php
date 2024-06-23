<?php
namespace Autumn\Exceptions;

class ForbiddenException extends RequestException
{
    public const ERROR_CODE = 403;
    public const ERROR_MESSAGE = 'Forbidden';
}