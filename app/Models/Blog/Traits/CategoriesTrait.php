<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/11
 */

namespace App\Models\Blog\Traits;

use App\Models\Blog\Category;
use Autumn\Database\Attributes\Transient;

trait CategoriesTrait
{
    /**
     * @var Category[]
     */
    #[Transient]
    private iterable $categories = [];

    public function getCategories(): iterable
    {
        return $this->categories;
    }

    /**
     * @param Category[] $categories
     */
    public function setCategories(iterable $categories): void
    {
        $this->categories = $categories;
    }
}