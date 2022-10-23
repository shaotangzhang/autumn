<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/14
 */

namespace App\Services;

use App\Services\Blog\BlogServicesTrait;
use App\Services\Traits\GlobalServiceTrait;

abstract class AbstractService
{
    use GlobalServiceTrait;
    use BlogServicesTrait;
}