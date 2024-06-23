<?php
namespace Autumn\Exceptions;

class LockedException extends RequestException
{
    public const ERROR_CODE = 423;
    public const ERROR_MESSAGE = 'Locked';
}