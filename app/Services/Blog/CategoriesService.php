<?php
/**
 * Autumn PHP Framework
 *
 * Date:        2022/10/11
 */

namespace App\Services\Blog;

use App\Models\Blog\Category;
use App\Services\AbstractService;
use Autumn\Database\Interfaces\ResultSetInterface;
use Autumn\Http\Exceptions\ConflictException;
use Autumn\Http\Exceptions\NotFoundException;
use Autumn\System\Attributes\Service;
use RuntimeException;

#[Service]
class CategoriesService extends AbstractService
{
    public function getCategoryType(string $type): string
    {
        return 'category:' . $type;
    }

    public function findCategory(int $id, string $type=null): ?Category
    {
        Category::withoutTrashed()
            ->with('siteId', $this->getSiteId())
            ->when($type, 'type', $this->getCategoryType($type));

        return Category::find($id);
    }

    /**
     * @throws NotFoundException
     */
    public function findCategoryOrFail(int $id, string $type = null): Category
    {
        Category::withoutTrashed()
            ->with('siteId', $this->getSiteId())
            ->when($type, 'type', $this->getCategoryType($type));

        return Category::findOrFail($id);
    }

    public function loadCategories(string $type, ?int $limit = null, ?int $page = null): ResultSetInterface
    {
        return Category::withoutTrashed()
            ->with('siteId', $this->getSiteId())
            ->with('type', $this->getCategoryType($type))
            ->limit($limit, $page)
            ->query();
    }

    public function loadCategoryTree(string $type, int $rootId = 0): array
    {
        $list = [];
        $tree = [];

        foreach ($this->loadCategories($type) as $category) {
            $list[$id = $category['id']] = $category;
            $tree[$category['parentId']]['items'][$id] = &$list[$id];
        }

        return $tree[$rootId]['items'] ?? [];
    }

    public function createCategory(Category $category): Category
    {
        if (!$category->isNew()) {
            throw new RuntimeException('The given data is not a new record.');
        }

        if ($category->save()) {
            return $category;
        }

        throw new ConflictException('Not created.');
    }
}