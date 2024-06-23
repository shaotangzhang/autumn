<?php
namespace Autumn\Database;

use Autumn\Exceptions\ServerException;

class DbException extends ServerException
{
    public const ERROR_MESSAGE = 'Database Exception';
}