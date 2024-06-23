<?php
/**
 * Autumn PHP Framework
 *
 * Date:        17/06/2024
 */

namespace App\Controllers;

use Autumn\System\Request;

class PostController extends AbstractController
{
    public function index(Request $request): mixed
    {
        return $this->view('posts/index', [
            'items' => Post::latest($request, [
                'limit_default' => 24,
                'limit_max' => 100,
            ])
        ]);
    }

    public function show(Post $post): mixed
    {
        return $this->view('posts/show', [
            'item' => $post
        ]);
    }
}