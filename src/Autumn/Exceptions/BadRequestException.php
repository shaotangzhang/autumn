<?php
namespace Autumn\Exceptions;

class BadRequestException extends RequestException
{
    public const ERROR_CODE = 400;
    public const ERROR_MESSAGE = 'Bad Request';
}