<?php
/**
 * Autumn PHP Framework
 *
 * Date:        24/05/2024
 */

namespace Console\Controllers;

use Autumn\System\Controller;

class IndexController extends Controller
{
    public function index(): mixed
    {
        return new \DateTimeImmutable;
    }
}