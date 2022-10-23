<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/7
 */

namespace App\Controllers\Login;

use App\Models\Auth\User;
use App\Models\Blog\Site;
use Autumn\Database\Connection;
use Autumn\System\Controller;
use Autumn\System\Model;
use Autumn\System\View;

class RegisterController extends Controller
{
    public function index(): View
    {
        User::leftJoin(Site::class, 'siteId', 'site', 'site.id');
        debug(User::search()->fetchAll());

        return $this->fetch('login/register');
    }
}