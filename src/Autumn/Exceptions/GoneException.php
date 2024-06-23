<?php
namespace Autumn\Exceptions;

class GoneException extends RequestException
{
    public const ERROR_CODE = 410;
    public const ERROR_MESSAGE = 'Gone';
}