<?php
/**
 * Autumn PHP Framework
 *
 * Date:        29/06/2024
 */

namespace App\Admin;

use Autumn\System\Controller;

class IndexController extends Controller
{
    public function index(): mixed
    {
        return 'Hello admin!';
    }
}