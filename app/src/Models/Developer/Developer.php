<?php
/**
 * Autumn PHP Framework
 *
 * Date:        27/06/2024
 */

namespace App\Models\Developer;

use Autumn\Extensions\Auth\Models\User\User;

class Developer extends User
{
    public const DEFAULT_TYPE = 'developer';
}