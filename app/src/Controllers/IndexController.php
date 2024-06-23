<?php
/**
 * Autumn PHP Framework
 *
 * Date:        23/06/2024
 */

namespace App\Controllers;

use Autumn\System\Controller;
use Autumn\System\Request;

class IndexController extends Controller
{
    public function index(?string $name, Request $request): mixed
    {
        return 'Hello ' . ($name ?: $request::host()) . '!';
    }
}