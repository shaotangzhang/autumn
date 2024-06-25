<?php
/**
 * Autumn PHP Framework
 *
 * Date:        7/05/2024
 */

namespace Autumn\Database;

use Autumn\Exceptions\ServerException;

class DbException extends ServerException
{
    public const ERROR_MESSAGE = 'Database Exception';
}