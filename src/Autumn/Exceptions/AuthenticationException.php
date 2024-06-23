<?php
namespace Autumn\Exceptions;

class AuthenticationException extends UnauthorizedException
{
    public const ERROR_MESSAGE = 'Login is required';
}