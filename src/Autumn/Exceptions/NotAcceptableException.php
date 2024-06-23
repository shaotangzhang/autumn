<?php
namespace Autumn\Exceptions;

class NotAcceptableException extends RequestException
{
    public const ERROR_CODE = 406;
    public const ERROR_MESSAGE = 'Not Acceptable';
}