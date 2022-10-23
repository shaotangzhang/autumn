<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/9
 */

namespace App\Controllers\Blog;

use App\Services\Blog\BlogServicesTrait;
use Autumn\System\Controller;

class AbstractController extends Controller
{
    use BlogServicesTrait;

    protected string $templateLayout = '/blog/common/layout';
    protected string $templatePrefix = '/blog/';
}