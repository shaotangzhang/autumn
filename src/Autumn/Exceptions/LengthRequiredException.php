<?php
namespace Autumn\Exceptions;

class LengthRequiredException extends RequestException
{
    public const ERROR_CODE = 410;
    public const ERROR_MESSAGE = 'Length Required';
}