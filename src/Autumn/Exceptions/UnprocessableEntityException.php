<?php
namespace Autumn\Exceptions;

class UnprocessableEntityException extends RequestException
{
    public const ERROR_CODE = 422;
    public const ERROR_MESSAGE = 'Unprocessable Entity';
}