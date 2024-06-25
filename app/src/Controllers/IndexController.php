<?php
/**
 * Autumn PHP Framework
 *
 * Date:        23/06/2024
 */

namespace App\Controllers;

use Autumn\System\Controller;
use Autumn\System\Request;
use Autumn\System\View;

class IndexController extends Controller
{
    public function index(?string $name, Request $request): mixed
    {
        $this->loadLang('home');

        return $this->view('home/index', $request->toArray());
    }
}