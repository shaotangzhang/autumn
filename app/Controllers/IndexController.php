<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/5
 */

namespace App\Controllers;

use Autumn\Http\Exceptions\RedirectException;
use Autumn\System\Session;
use Autumn\System\View;

class IndexController extends AbstractController
{
    public function index(?string $name, ?int $n)
    {
        return $this->fetch('home/index', [
            'title'=>$name ?: $this->config('name'),
        ]);
    }

    public function show(int $id, ?string $name): View
    {
        return $this->fetch('home/index', [
            'title'=>$name . '@'. $id,
        ]);
    }
}