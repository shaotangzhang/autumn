<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/9
 */

namespace App\Services\Blog;

use Autumn\App;

trait BlogServicesTrait
{
    public function getMediaService(): MediaService
    {
        return App::factory(MediaService::class);
    }

    public function getCategoriesService(): CategoriesService
    {
        return App::factory(CategoriesService::class);
    }
}