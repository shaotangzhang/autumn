<?php
namespace Autumn\Exceptions;

use Throwable;

class UnsupportedMediaTypeException extends RequestException
{
    public const ERROR_CODE = 415;
    public const ERROR_MESSAGE = 'Unsupported Media Type';
}