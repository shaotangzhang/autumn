<?php
namespace Autumn\Exceptions;

class NotFoundException extends RequestException
{
    public const ERROR_CODE = 404;
    public const ERROR_MESSAGE = 'Not Found';
}