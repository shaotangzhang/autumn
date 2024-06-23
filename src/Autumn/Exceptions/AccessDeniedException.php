<?php
namespace Autumn\Exceptions;

class AccessDeniedException extends UnauthorizedException
{
    public const ERROR_CODE = 403;
    public const ERROR_MESSAGE = 'Access Denied';
}