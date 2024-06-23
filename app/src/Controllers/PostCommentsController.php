<?php
/**
 * Autumn PHP Framework
 *
 * Date:        17/06/2024
 */

namespace App\Controllers;

use App\Forms\PostCommentForm;

class PostCommentsController extends AbstractController
{
    public function post(PostCommentForm $form)
    {
        $form->validate();

        $post = $form->post();
        if (!$post) {
            throw NotFoundException::of('Post is not found.');
        }

        return $post->comments()->create($form->comment());
    }
}