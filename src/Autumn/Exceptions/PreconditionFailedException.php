<?php
namespace Autumn\Exceptions;

class PreconditionFailedException extends RequestException
{
    public const ERROR_CODE = 412;
    public const ERROR_MESSAGE = 'Precondition Failed';
}